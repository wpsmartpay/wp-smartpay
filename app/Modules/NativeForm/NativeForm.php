<?php

namespace SmartPay\Modules\NativeForm;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the `smartpay_form` CPT, its REST API, [sp_form] shortcode,
 * Gutenberg sidebar, and payment data fix — ported from the pro plugin.
 *
 * @package SmartPay\Modules\NativeForm
 */
class NativeForm {

	/**
	 * @param mixed $app
	 */
	public function __construct( $app ) {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_sidebar_assets' ) );
		add_filter( 'block_categories_all', array( $this, 'add_block_category' ), 10, 2 );
		add_filter( 'preview_post_link', array( $this, 'filter_preview_url' ), 10, 2 );
		add_filter( 'template_include', array( $this, 'serve_preview_template' ) );
		add_filter( 'manage_smartpay_form_posts_columns', array( $this, 'add_shortcode_column' ) );
		add_action( 'manage_smartpay_form_posts_custom_column', array( $this, 'render_shortcode_column' ), 10, 2 );
		add_action( 'admin_footer-edit.php', array( $this, 'shortcode_column_script' ) );
		add_filter( 'smartpay_prepare_payment_data', array( $this, 'fix_cpt_form_payment_data' ), 5, 2 );
		add_action( 'save_post_smartpay_form', array( $this, 'sync_pricing_block_amounts' ), 20, 2 );
		add_filter( 'render_block_smartpay-form/goal-progress', 'smartpay_render_goal_progress_block', 10, 2 );
	}

	/**
	 * Sync the Pricing block's options into the `_smartpay_amounts` post meta.
	 *
	 * When a `smartpay-form/pricing` block is present in the form, it is the
	 * source of truth for pricing. Its options are written to the meta so the
	 * frontend template default selection and server-side payment validation
	 * (which trust the stored amounts, not the posted amount) stay correct.
	 *
	 * @param int      $post_id Saved form post ID.
	 * @param \WP_Post $post    Saved form post object.
	 */
	public function sync_pricing_block_amounts( $post_id, $post ): void {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! ( $post instanceof \WP_Post ) || 'smartpay_form' !== $post->post_type ) {
			return;
		}

		if ( ! has_block( 'smartpay-form/pricing', $post ) ) {
			return; // No block — leave sidebar-managed amounts untouched.
		}

		$blocks  = parse_blocks( $post->post_content );
		$pricing = $this->find_block( $blocks, 'smartpay-form/pricing' );
		if ( null === $pricing ) {
			return;
		}

		// Options are the parent's child `smartpay-form/pricing-option` blocks.
		$children = isset( $pricing['innerBlocks'] ) && is_array( $pricing['innerBlocks'] )
			? $pricing['innerBlocks']
			: array();
		$pro      = function_exists( 'smartpay_is_pro_active' ) && smartpay_is_pro_active();
		$amounts  = array();

		foreach ( $children as $child ) {
			if ( ! is_array( $child ) || 'smartpay-form/pricing-option' !== ( $child['blockName'] ?? '' ) ) {
				continue;
			}
			$item = isset( $child['attrs'] ) && is_array( $child['attrs'] ) ? $child['attrs'] : array();

			$billing_type = sanitize_text_field( $item['billing_type'] ?? 'One Time' );
			// Subscriptions are Pro-only — downgrade defensively without Pro.
			if ( 'Subscription' === $billing_type && ! $pro ) {
				$billing_type = 'One Time';
			}

			$key = sanitize_key( $item['key'] ?? '' );
			if ( '' === $key ) {
				$key = 'opt-' . wp_generate_password( 9, false, false );
			}

			$amount = array(
				'key'          => $key,
				'label'        => sanitize_text_field( $item['label'] ?? '' ),
				'description'  => sanitize_text_field( $item['description'] ?? '' ),
				'amount'       => max( 0, (float) ( $item['amount'] ?? 0 ) ),
				'billing_type' => $billing_type,
			);

			if ( 'Subscription' === $billing_type ) {
				$amount['billing_period'] = sanitize_text_field( $item['billing_period'] ?? 'month' );
				$amount['setup_fee']      = max( 0, (float) ( $item['setup_fee'] ?? 0 ) );
				$amount['billing_cycle']  = sanitize_text_field( (string) ( $item['billing_cycle'] ?? '' ) );
			}

			$amounts[] = $amount;
		}

		if ( ! empty( $amounts ) ) {
			update_post_meta( $post_id, '_smartpay_amounts', wp_json_encode( $amounts ) );
		}
	}

	/**
	 * Recursively locate the first block matching $name within a parsed tree.
	 *
	 * @param array  $blocks Parsed blocks (from parse_blocks()).
	 * @param string $name   Block name to find.
	 * @return array|null    The matching block array, or null.
	 */
	private function find_block( array $blocks, string $name ) {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && $name === $block['blockName'] ) {
				return $block;
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$found = $this->find_block( $block['innerBlocks'], $name );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	/**
	 * Register the `smartpay_form` CPT and post meta.
	 */
	public function register_post_type(): void {
		register_post_type(
			'smartpay_form',
			array(
				'labels'             => array(
					'name'          => __( 'WPSmartPay Forms', 'smartpay' ),
					'singular_name' => __( 'WPSmartPay Form', 'smartpay' ),
					'edit_item'     => __( 'Edit Form', 'smartpay' ),
					'view_item'     => __( 'View Form', 'smartpay' ),
				),
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'show_in_rest'       => true,
				'supports'           => array( 'title', 'editor', 'custom-fields', 'revisions' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
				// Seed a new form with the essential blocks: name, email, a pricing
				// option, and the submit button. Authors can edit/remove from here.
				'template'           => array(
					array( 'smartpay-form/name' ),
					array(
						'smartpay-form/email',
						array(),
						array(
							array( 'smartpay-form/email-label' ),
							array( 'smartpay-form/email-input' ),
						),
					),
					array(
						'smartpay-form/pricing',
						array(),
						array(
							array(
								'smartpay-form/pricing-option',
								array(
									'label'  => 'Basic',
									'amount' => '0',
								),
							),
						),
					),
					array(
						'smartpay-form/submit-button',
						array(),
						array(
							array( 'smartpay-form/submit-coupon' ),
							array( 'smartpay-form/submit-pay' ),
						),
					),
				),
			)
		);

		$this->register_post_meta();
	}

	/**
	 * Register REST-accessible post meta for pricing and settings.
	 */
	public function register_post_meta(): void {
		$auth_callback = function () {
			return current_user_can( 'edit_posts' );
		};

		register_post_meta(
			'smartpay_form',
			'_smartpay_amounts',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '[]',
				'auth_callback'     => $auth_callback,
				'revisions_enabled' => true,
			)
		);

		register_post_meta(
			'smartpay_form',
			'_smartpay_settings',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '{}',
				'auth_callback'     => $auth_callback,
				'revisions_enabled' => true,
			)
		);

		register_post_meta(
			'smartpay_form',
			'_smartpay_form_id',
			array(
				'show_in_rest'  => false,
				'single'        => true,
				'type'          => 'integer',
				'default'       => 0,
				'auth_callback' => $auth_callback,
			)
		);

		register_post_meta(
			'smartpay_form',
			'_sp_form_tax_override',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'auth_callback'     => $auth_callback,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	/**
	 * Register the "WP SmartPay" block category.
	 *
	 * @param array                    $categories
	 * @param \WP_Block_Editor_Context $context
	 * @return array
	 */
	public function add_block_category( array $categories, $context ): array {
		return array_merge(
			array(
				array(
					'slug'  => 'wp-smartpay',
					'title' => __( 'WPSmartPay', 'smartpay' ),
					'icon'  => null,
				),
			),
			$categories
		);
	}

	/**
	 * Register GET + DELETE REST endpoints for the native forms React list.
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'smartpay/v1',
			'native-forms',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'rest_get_forms' ),
					'permission_callback' => fn() => current_user_can( 'manage_options' ),
					'args'                => array(
						'page'     => array(
							'default'           => 1,
							'sanitize_callback' => 'absint',
						),
						'per_page' => array(
							'default'           => 10,
							'sanitize_callback' => 'absint',
						),
						'search'   => array(
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'rest_delete_form' ),
					'permission_callback' => fn() => current_user_can( 'manage_options' ),
				),
			)
		);

		register_rest_route(
			'smartpay/v1',
			'native-forms/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'rest_delete_form' ),
					'permission_callback' => fn() => current_user_can( 'manage_options' ),
				),
			)
		);

		register_rest_route(
			'smartpay/v1',
			'migrate-legacy-form',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'rest_migrate_legacy_form' ),
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
				'args'                => array(
					'form_id' => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'migrate_all' => array(
						'required'          => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
					'dry_run' => array(
						'required'          => false,
						'default'           => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);
	}

	/**
	 * REST handler: migrate one or all legacy forms to CPT posts.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rest_migrate_legacy_form( \WP_REST_Request $request ) {
		if ( ! class_exists( '\\SmartPay\\Models\\Form' ) ) {
			return new \WP_Error( 'smartpay_no_legacy_model', __( 'Legacy Form model not available.', 'smartpay' ), array( 'status' => 500 ) );
		}

		$migrator = new LegacyFormMigrator();
		$dry_run  = (bool) $request->get_param( 'dry_run' );
		$form_id  = (int) $request->get_param( 'form_id' );
		$all      = (bool) $request->get_param( 'migrate_all' );

		if ( ! $form_id && ! $all ) {
			return new \WP_Error( 'smartpay_missing_param', __( 'Provide form_id or migrate_all=true.', 'smartpay' ), array( 'status' => 400 ) );
		}

		$forms = $all
			? \SmartPay\Models\Form::all()
			: \SmartPay\Models\Form::where( 'id', $form_id )->get();

		$results = array();
		foreach ( $forms as $form ) {
			$result = $migrator->migrate( $form, $dry_run );
			$results[] = array(
				'legacy_id' => $form->id,
				'title'     => $form->title,
				'post_id'   => is_wp_error( $result ) ? null : $result,
				'error'     => is_wp_error( $result ) ? $result->get_error_message() : null,
				'dry_run'   => $dry_run,
			);
		}

		return new \WP_REST_Response( array( 'results' => $results ), 200 );
	}

	/**
	 * REST handler: paginated list of native CPT forms.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function rest_get_forms( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );
		$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );

		$args = array(
			'post_type'      => 'smartpay_form',
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$query  = new \WP_Query( $args );
		$total  = (int) $query->found_posts;
		$offset = ( $page - 1 ) * $per_page;

		$forms = array_map(
			function ( \WP_Post $post ) {
				$settings = $this->decode_meta_json( get_post_meta( $post->ID, '_smartpay_settings', true ), array() );
				$goal     = $settings['goal'] ?? array();

				$goal_data = null;
				if ( ! empty( $goal['enabled'] ) && function_exists( 'smartpay_calculate_goal_progress' ) ) {
					$progress  = smartpay_calculate_goal_progress( (int) $post->ID );
					$goal_data = array(
						'enabled'      => true,
						'current'      => $progress['current'],
						'target'       => $progress['target'],
						'percentage'   => $progress['percentage'],
						'goal_reached' => $progress['goal_reached'],
						'type'         => $goal['type'] ?? 'quantity',
					);
				}

				return array(
					'id'          => $post->ID,
					'title'       => $post->post_title ?: __( '(Untitled)', 'smartpay' ),
					'status'      => $post->post_status,
					'date'        => get_the_date( 'Y-m-d H:i', $post->ID ),
					'shortcode'   => '[sp_form id="' . absint( $post->ID ) . '"]',
					'edit_url'    => admin_url( 'post.php?post=' . absint( $post->ID ) . '&action=edit' ),
					'preview_url' => get_the_permalink( $post->ID ),
					'goal'        => $goal_data,
				);
			},
			$query->posts
		);

		return new \WP_REST_Response(
			array(
				'forms' => array(
					'data'         => $forms,
					'current_page' => $page,
					'per_page'     => $per_page,
					'last_page'    => max( 1, (int) ceil( $total / $per_page ) ),
					'total'        => $total,
					'from'         => $total > 0 ? $offset + 1 : 0,
					'to'           => min( $offset + $per_page, $total ),
				),
			)
		);
	}

	/**
	 * REST handler: permanently delete a native CPT form.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function rest_delete_form( \WP_REST_Request $request ): \WP_REST_Response {
		$id   = absint( $request->get_param( 'id' ) );
		$post = $id ? get_post( $id ) : null;

		if ( ! $post || 'smartpay_form' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'message' => __( 'Form not found.', 'smartpay' ) ), 404 );
		}

		$result = wp_delete_post( $id, true );

		if ( ! $result ) {
			return new \WP_REST_Response( array( 'message' => __( 'Could not delete form.', 'smartpay' ) ), 500 );
		}

		return new \WP_REST_Response( array( 'message' => __( 'Form deleted.', 'smartpay' ) ) );
	}

	/**
	 * Register the [sp_form] shortcode.
	 */
	public function register_shortcode(): void {
		add_shortcode( 'sp_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * [sp_form] shortcode handler — renders a CPT form inline.
	 *
	 * @param array|string $atts
	 * @return string
	 */
	public function render_shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'id'       => 0,
				'behavior' => 'embedded',
			),
			$atts,
			'sp_form'
		);

		$post_id = absint( $atts['id'] );
		if ( ! $post_id ) {
			return '';
		}

		$post = get_post( $post_id );
		if ( ! $post || 'smartpay_form' !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		$amounts  = $this->decode_meta_json( get_post_meta( $post_id, '_smartpay_amounts', true ), array() );
		$settings = $this->decode_meta_json( get_post_meta( $post_id, '_smartpay_settings', true ), array() );
		$body     = $post->post_content;

		if ( empty( $amounts ) ) {
			$amounts = array(
				array(
					'key'          => 'default',
					'label'        => '',
					'amount'       => '0.00',
					'billing_type' => 'One Time',
				),
			);
		}

		$theme_override = locate_template( 'smartpay/native-form-embed.php' );
		$template       = $theme_override ?: plugin_dir_path( SMARTPAY_PLUGIN_FILE ) . 'resources/views/native-form-embed.php';
		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include $template;
		return ob_get_clean();
	}

	/**
	 * Enqueue the Gutenberg editor sidebar assets on smartpay_form edit pages.
	 *
	 * @param string $hook
	 */
	public function enqueue_sidebar_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'smartpay_form' !== $screen->post_type ) {
			return;
		}

		if ( ! wp_script_is( 'smartpay-ui', 'registered' ) ) {
			wp_register_script(
				'smartpay-ui',
				SMARTPAY_PLUGIN_ASSETS . '/js/ui.js',
				array( 'wp-element', 'wp-data' ),
				SMARTPAY_VERSION,
				true
			);
		}

		if ( ! wp_script_is( 'smartpay-form', 'registered' ) ) {
			wp_register_script(
				'smartpay-form',
				SMARTPAY_PLUGIN_ASSETS . '/form-builder/index.js',
				array(
					'lodash',
					'wp-block-editor',
					'wp-block-library',
					'wp-blocks',
					'wp-components',
					'wp-data',
					'wp-dom-ready',
					'wp-editor',
					'wp-element',
					'wp-format-library',
					'wp-i18n',
					'wp-media-utils',
					'wp-plugins',
					'wp-polyfill',
					'wp-primitives',
					'smartpay-ui',
				),
				SMARTPAY_VERSION,
				false
			);
		}

		wp_enqueue_script( 'smartpay-form' );

		if ( ! wp_style_is( 'smartpay-form', 'registered' ) ) {
			wp_register_style(
				'smartpay-form',
				SMARTPAY_PLUGIN_ASSETS . '/form-builder/index.css',
				array( 'wp-edit-blocks' ),
				SMARTPAY_VERSION
			);
		}
		wp_enqueue_style( 'smartpay-form' );

		wp_localize_script(
			'smartpay-form',
			'smartpay',
			array(
				'restUrl'  => get_rest_url( '', 'smartpay' ),
				'adminUrl' => admin_url( 'admin.php' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'apiNonce' => wp_create_nonce( 'wp_rest' ),
				'logo'     => SMARTPAY_PLUGIN_ASSETS . '/img/logo-lockup-color.png',
				'isPro'    => smartpay_is_pro_active(),
			)
		);

		// Dedicated object for the Pricing block — collision-proof (the shared
		// `smartpay` object is localized by multiple modules on this handle).
		wp_localize_script(
			'smartpay-form',
			'smartpayPricingData',
			array(
				'isPro'      => smartpay_is_pro_active(),
				'upgradeUrl' => 'https://wpsmartpay.com/pricing',
			)
		);

		wp_add_inline_script(
			'smartpay-form',
			'window.smartPayBlockEditorSettings = window.smartPayBlockEditorSettings || ' . wp_json_encode( $this->get_editor_settings() ) . ';'
		);

		wp_add_inline_script(
			'wp-blocks',
			'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( get_block_editor_server_block_settings() ) . ');'
		);

		wp_add_inline_script(
			'smartpay-form',
			$this->get_block_category_script(),
			'after'
		);

		// Form editor sidebar — pricing + settings panels.
		$public_dir  = plugin_dir_path( SMARTPAY_PLUGIN_FILE ) . 'public';
		$sidebar_js  = SMARTPAY_PLUGIN_ASSETS . '/js/admin/form-editor-sidebar.js';
		$sidebar_css = SMARTPAY_PLUGIN_ASSETS . '/css/admin/form-editor-sidebar.css';

		if ( file_exists( $public_dir . '/js/admin/form-editor-sidebar.js' ) ) {
			wp_enqueue_script(
				'smartpay-form-editor-sidebar',
				$sidebar_js,
				array( 'wp-plugins', 'wp-edit-post', 'wp-editor', 'wp-components', 'wp-element', 'wp-data', 'wp-i18n', 'wp-core-data' ),
				SMARTPAY_VERSION,
				true
			);

			wp_set_script_translations( 'smartpay-form-editor-sidebar', 'smartpay' );

			wp_localize_script(
				'smartpay-form-editor-sidebar',
				'smartpayFormEditor',
				array(
					'logoUrl'      => SMARTPAY_PLUGIN_ASSETS . '/img/favicon.png',
					'formsListUrl' => admin_url( 'admin.php?page=smartpay' ) . '#/native-forms',
				)
			);
		}

		if ( file_exists( $public_dir . '/css/admin/form-editor-sidebar.css' ) ) {
			wp_enqueue_style(
				'smartpay-form-editor-sidebar',
				$sidebar_css,
				array( 'wp-components' ),
				SMARTPAY_VERSION
			);
		}

		// Inject template blocks when ?sp_template=ID is present on post-new.php.
		if ( 'post-new.php' === $hook ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$template_id = absint( $_GET['sp_template'] ?? 0 );
			if ( $template_id > 0 ) {
				$this->inject_template_blocks( $template_id );
			}
		}
	}

	/**
	 * Inject window.spTemplateBlocks + window.spTemplateMeta for template import.
	 *
	 * The blocks payload is a nested tree of { name, attrs, innerBlocks } objects
	 * consumed by the recursive builder in form-editor-sidebar/index.js, so each
	 * composite field (parent → label + input/options children) carries its attrs.
	 *
	 * @param int $template_id Template ID from the UI template library.
	 */
	private function inject_template_blocks( int $template_id ): void {
		$definition = $this->get_template_definition( $template_id );
		if ( ! $definition ) {
			return;
		}

		$meta = array( 'amounts' => $definition['amounts'] );

		wp_add_inline_script(
			'smartpay-form-editor-sidebar',
			'window.spTemplateBlocks = ' . wp_json_encode( $definition['blocks'] ) . ';' .
			'window.spTemplateMeta = ' . wp_json_encode( $meta ) . ';',
			'before'
		);
	}

	// ── Template block-tree builders ────────────────────────────────────────

	/**
	 * Build a single block node for the template tree.
	 *
	 * @param string $name  Block name.
	 * @param array  $attrs Block attributes (empty → JSON object, not array).
	 * @param array  $inner Child block nodes.
	 * @return array
	 */
	private function tpl_block( string $name, array $attrs = array(), array $inner = array() ): array {
		return array(
			'name'        => $name,
			'attrs'       => empty( $attrs ) ? new \stdClass() : $attrs,
			'innerBlocks' => $inner,
		);
	}

	/**
	 * Name field. Inner sub-fields are auto-generated from the locked template;
	 * the parent attrs toggle which parts show.
	 *
	 * @param bool $middle Whether to show the middle-name sub-field.
	 * @return array
	 */
	private function tpl_name( bool $middle = false ): array {
		return $this->tpl_block(
			'smartpay-form/name',
			array(
				'showFirstName'  => true,
				'showMiddleName' => $middle,
				'showLastName'   => true,
			)
		);
	}

	/**
	 * Email field (label + input).
	 *
	 * @param string $label Label text.
	 * @return array
	 */
	private function tpl_email( string $label = 'Email Address' ): array {
		return $this->tpl_block(
			'smartpay-form/email',
			array(),
			array(
				$this->tpl_block( 'smartpay-form/email-label', array( 'text' => $label ) ),
				$this->tpl_block(
					'smartpay-form/email-input',
					array(
						'fieldName'   => 'email',
						'placeholder' => 'you@example.com',
						'isRequired'  => true,
					)
				),
			)
		);
	}

	/**
	 * Single-line text input (label + input). inputType drives the real HTML
	 * input type on the frontend (text|number|email|tel|date|time|url).
	 *
	 * @param string $label       Field label text.
	 * @param string $field       Unique submission field name.
	 * @param string $type        HTML input type.
	 * @param string $placeholder Placeholder text.
	 * @param bool   $required    Whether the field is required.
	 * @return array
	 */
	private function tpl_text( string $label, string $field, string $type = 'text', string $placeholder = '', bool $required = false ): array {
		return $this->tpl_block(
			'smartpay-form/text-input',
			array(),
			array(
				$this->tpl_block( 'smartpay-form/text-input-label', array( 'text' => $label ) ),
				$this->tpl_block(
					'smartpay-form/text-input-input',
					array(
						'fieldName'   => $field,
						'inputType'   => $type,
						'placeholder' => $placeholder,
						'isRequired'  => $required,
					)
				),
			)
		);
	}

	/**
	 * Multi-line textarea (label + input).
	 *
	 * @param string $label       Field label text.
	 * @param string $field       Unique submission field name.
	 * @param string $placeholder Placeholder text.
	 * @param int    $rows        Visible rows.
	 * @param bool   $required    Whether the field is required.
	 * @return array
	 */
	private function tpl_textarea( string $label, string $field, string $placeholder = '', int $rows = 4, bool $required = false ): array {
		return $this->tpl_block(
			'smartpay-form/textarea-input',
			array(),
			array(
				$this->tpl_block( 'smartpay-form/textarea-input-label', array( 'text' => $label ) ),
				$this->tpl_block(
					'smartpay-form/textarea-input-input',
					array(
						'fieldName'   => $field,
						'placeholder' => $placeholder,
						'isRequired'  => $required,
						'rows'        => $rows,
					)
				),
			)
		);
	}

	/**
	 * Choice field: select | radio | checkbox (label + options input).
	 *
	 * @param string $type     'select' | 'radio' | 'checkbox'.
	 * @param string $label    Field label text.
	 * @param string $field    Unique submission field name.
	 * @param array  $options  Plain label strings (value auto-slugged).
	 * @param string $selected Default selected value.
	 * @return array
	 */
	private function tpl_choice( string $type, string $label, string $field, array $options, string $selected = '' ): array {
		$parent = 'smartpay-form/' . $type . '-input';

		return $this->tpl_block(
			$parent,
			array(),
			array(
				$this->tpl_block( $parent . '-label', array( 'text' => $label ) ),
				$this->tpl_block(
					$parent . '-input',
					array(
						'fieldName'    => $field,
						'defaultValue' => $selected,
						'options'      => $this->tpl_options( $options ),
					)
				),
			)
		);
	}

	/**
	 * Normalise option labels into [{ value, label }] pairs.
	 *
	 * @param array $options Plain option label strings.
	 * @return array
	 */
	private function tpl_options( array $options ): array {
		$out = array();
		foreach ( $options as $label ) {
			$out[] = array(
				'value' => sanitize_title( $label ),
				'label' => $label,
			);
		}
		return $out;
	}

	/**
	 * Full mailing-address field (all lines on).
	 *
	 * @return array
	 */
	private function tpl_address(): array {
		return $this->tpl_block(
			'smartpay-form/address-input',
			array(
				'showLine1'   => true,
				'showLine2'   => true,
				'showCity'    => true,
				'showState'   => true,
				'showZip'     => true,
				'showCountry' => true,
			)
		);
	}

	/**
	 * Pricing block with one option per price entry.
	 *
	 * @param array  $prices Each: [ 'label', 'amount', 'description'? ].
	 * @param string $preset 'grid' | 'list'.
	 * @return array
	 */
	private function tpl_pricing( array $prices, string $preset = 'grid' ): array {
		$options = array();
		foreach ( $prices as $i => $p ) {
			$options[] = $this->tpl_block(
				'smartpay-form/pricing-option',
				array(
					'key'          => 'opt_' . ( $i + 1 ),
					'label'        => $p['label'],
					'description'  => $p['description'] ?? '',
					'amount'       => (string) $p['amount'],
					'billing_type' => 'One Time',
				)
			);
		}

		return $this->tpl_block( 'smartpay-form/pricing', array( 'preset' => $preset ), $options );
	}

	/**
	 * Submit (pay) button with coupon + pay children.
	 *
	 * @param string $label Pay button label.
	 * @return array
	 */
	private function tpl_pay( string $label = 'Pay Now' ): array {
		return $this->tpl_block(
			'smartpay-form/submit-button',
			array(),
			array(
				$this->tpl_block( 'smartpay-form/submit-coupon' ),
				$this->tpl_block( 'smartpay-form/submit-pay', array( 'label' => $label ) ),
			)
		);
	}

	/**
	 * Derive the `_smartpay_amounts` meta payload from a price list (kept in sync
	 * with the pricing-option blocks built by tpl_pricing()).
	 *
	 * @param array $prices Price list (each: label, amount).
	 * @return array
	 */
	private function pricing_amounts( array $prices ): array {
		$amounts = array();
		foreach ( $prices as $i => $p ) {
			$amounts[] = array(
				'key'          => 'opt_' . ( $i + 1 ),
				'label'        => $p['label'],
				'amount'       => number_format( (float) $p['amount'], 2, '.', '' ),
				'billing_type' => 'One Time',
			);
		}
		return $amounts;
	}

	/**
	 * Assemble a template definition: field blocks + pricing + pay button, with
	 * matching amounts meta.
	 *
	 * @param string $name   Template name.
	 * @param array  $fields Field block nodes (in order).
	 * @param array  $prices Price list for the pricing block.
	 * @param string $pay    Pay button label.
	 * @param string $preset Pricing preset ('grid' | 'list').
	 * @return array
	 */
	private function tpl_assemble( string $name, array $fields, array $prices, string $pay = 'Pay Now', string $preset = 'grid' ): array {
		$blocks   = $fields;
		$blocks[] = $this->tpl_pricing( $prices, $preset );
		$blocks[] = $this->tpl_pay( $pay );

		return array(
			'name'    => $name,
			'blocks'  => $blocks,
			'amounts' => $this->pricing_amounts( $prices ),
		);
	}

	/**
	 * Return a template definition (nested block tree + amounts) by ID, or null.
	 *
	 * IDs map 1:1 to resources/js/admin/native-forms/templates.js. Categories:
	 * payment (1xxx), donation (2xxx), registration (3xxx), event (4xxx),
	 * survey (5xxx), contact (6xxx), booking (7xxx).
	 *
	 * @param int $id Template ID.
	 * @return array|null
	 */
	private function get_template_definition( int $id ): ?array {
		switch ( $id ) {
			// ── Payment ──────────────────────────────────────────────
			case 1001:
				return $this->tpl_assemble(
					'Simple Payment Form',
					array( $this->tpl_name(), $this->tpl_email() ),
					array(
						array(
							'label'  => 'Payment',
							'amount' => 25,
						),
					),
					'Pay Now'
				);

			case 1002:
				return $this->tpl_assemble(
					'Product Order Form',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_text( 'Quantity', 'quantity', 'number', '1' ),
						$this->tpl_address(),
						$this->tpl_textarea( 'Order Notes', 'order_notes', 'Anything we should know about your order?', 3 ),
					),
					array(
						array(
							'label'       => 'Basic',
							'amount'      => 19,
							'description' => 'Essential package',
						),
						array(
							'label'       => 'Standard',
							'amount'      => 39,
							'description' => 'Most popular',
						),
						array(
							'label'       => 'Premium',
							'amount'      => 59,
							'description' => 'Everything included',
						),
					),
					'Place Order'
				);

			case 1003:
				return $this->tpl_assemble(
					'Subscription Plans',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_choice( 'select', 'How did you hear about us?', 'referral_source', array( 'Search engine', 'Friend or colleague', 'Social media', 'Advertisement', 'Other' ) ),
					),
					array(
						array(
							'label'       => 'Monthly',
							'amount'      => 9,
							'description' => 'Billed monthly',
						),
						array(
							'label'       => 'Annual',
							'amount'      => 90,
							'description' => 'Save 16%',
						),
						array(
							'label'       => 'Lifetime',
							'amount'      => 249,
							'description' => 'One-time payment',
						),
					),
					'Subscribe',
					'list'
				);

			// ── Donation ─────────────────────────────────────────────
			case 2001:
				return $this->tpl_assemble(
					'Quick Donation',
					array( $this->tpl_name(), $this->tpl_email() ),
					array(
						array(
							'label'  => '$10',
							'amount' => 10,
						),
						array(
							'label'  => '$25',
							'amount' => 25,
						),
						array(
							'label'  => '$50',
							'amount' => 50,
						),
						array(
							'label'  => '$100',
							'amount' => 100,
						),
					),
					'Donate Now'
				);

			case 2002:
				return $this->tpl_assemble(
					'Charity Donation',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_choice( 'radio', 'Donation Frequency', 'frequency', array( 'One-time', 'Monthly', 'Annually' ), 'one-time' ),
						$this->tpl_textarea( 'Dedication Message', 'dedication', 'In honor or memory of…', 3 ),
						$this->tpl_choice( 'checkbox', 'Options', 'donation_options', array( 'Make my donation anonymous', 'Email me a receipt' ) ),
					),
					array(
						array(
							'label'  => '$25',
							'amount' => 25,
						),
						array(
							'label'  => '$50',
							'amount' => 50,
						),
						array(
							'label'  => '$100',
							'amount' => 100,
						),
						array(
							'label'  => '$250',
							'amount' => 250,
						),
					),
					'Give Now'
				);

			// ── Registration ─────────────────────────────────────────
			case 3001:
				return $this->tpl_assemble(
					'Newsletter Signup',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_choice( 'checkbox', 'Interests', 'interests', array( 'Product updates', 'Promotions', 'Events', 'Blog posts' ) ),
						$this->tpl_choice( 'checkbox', 'Consent', 'consent', array( 'I agree to receive marketing emails' ) ),
					),
					array(
						array(
							'label'  => 'Free',
							'amount' => 0,
						),
					),
					'Subscribe'
				);

			case 3002:
				return $this->tpl_assemble(
					'Membership Application',
					array(
						$this->tpl_name( true ),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_choice( 'select', 'Membership Level', 'level', array( 'Individual', 'Family', 'Student', 'Corporate' ) ),
						$this->tpl_address(),
						$this->tpl_textarea( 'Tell us about yourself', 'bio', 'A short introduction…', 4 ),
					),
					array(
						array(
							'label'  => 'Individual',
							'amount' => 49,
						),
						array(
							'label'  => 'Family',
							'amount' => 99,
						),
						array(
							'label'  => 'Student',
							'amount' => 25,
						),
						array(
							'label'  => 'Corporate',
							'amount' => 299,
						),
					),
					'Submit Application',
					'list'
				);

			// ── Event ────────────────────────────────────────────────
			case 4001:
				return $this->tpl_assemble(
					'Event Registration',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_text( 'Preferred Date', 'event_date', 'date' ),
						$this->tpl_choice( 'select', 'Ticket Type', 'ticket_type', array( 'General Admission', 'VIP', 'Group' ) ),
						$this->tpl_choice( 'checkbox', 'Dietary Requirements', 'dietary', array( 'Vegetarian', 'Vegan', 'Gluten-free', 'No restrictions' ) ),
					),
					array(
						array(
							'label'  => 'General',
							'amount' => 30,
						),
						array(
							'label'  => 'VIP',
							'amount' => 75,
						),
						array(
							'label'  => 'Group (4)',
							'amount' => 100,
						),
					),
					'Register'
				);

			case 4002:
				return $this->tpl_assemble(
					'Conference Registration',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Company', 'company' ),
						$this->tpl_text( 'Job Title', 'job_title' ),
						$this->tpl_text( 'Attendance Date', 'attend_date', 'date' ),
						$this->tpl_choice( 'radio', 'Primary Track', 'track', array( 'Engineering', 'Design', 'Product', 'Marketing' ) ),
						$this->tpl_choice( 'checkbox', 'Add-on Workshops', 'workshops', array( 'AI Workshop', 'UX Workshop', 'Leadership Workshop' ) ),
					),
					array(
						array(
							'label'       => 'Early Bird',
							'amount'      => 199,
							'description' => 'Limited time',
						),
						array(
							'label'  => 'Regular',
							'amount' => 299,
						),
						array(
							'label'       => 'Student',
							'amount'      => 99,
							'description' => 'Valid ID required',
						),
					),
					'Complete Registration'
				);

			// ── Survey ───────────────────────────────────────────────
			case 5001:
				return $this->tpl_assemble(
					'Customer Satisfaction Survey',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_choice( 'radio', 'How satisfied are you?', 'satisfaction', array( 'Very satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very dissatisfied' ) ),
						$this->tpl_choice( 'radio', 'Would you recommend us?', 'recommend', array( 'Definitely', 'Maybe', 'No' ) ),
						$this->tpl_textarea( 'Additional Comments', 'comments', 'What can we do better?', 4 ),
					),
					array(
						array(
							'label'  => 'Free',
							'amount' => 0,
						),
					),
					'Submit Survey'
				);

			case 5002:
				return $this->tpl_assemble(
					'Product Feedback Form',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_choice( 'radio', 'Product Quality', 'quality', array( 'Excellent', 'Good', 'Average', 'Poor' ) ),
						$this->tpl_choice( 'checkbox', 'Favorite Features', 'features', array( 'Ease of use', 'Design', 'Performance', 'Support', 'Price' ) ),
						$this->tpl_choice( 'radio', 'Likelihood to recommend', 'nps', array( 'High', 'Medium', 'Low' ) ),
						$this->tpl_textarea( 'Suggestions for improvement', 'suggestions', '', 4 ),
					),
					array(
						array(
							'label'  => 'Free',
							'amount' => 0,
						),
					),
					'Send Feedback'
				);

			// ── Contact ──────────────────────────────────────────────
			case 6001:
				return $this->tpl_assemble(
					'Contact & Payment Form',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_choice( 'radio', 'Inquiry Type', 'inquiry_type', array( 'General', 'Sales', 'Support', 'Billing' ) ),
						$this->tpl_textarea( 'Message', 'message', 'How can we help you?', 4 ),
					),
					array(
						array(
							'label'  => 'Consultation',
							'amount' => 50,
						),
					),
					'Submit'
				);

			case 6002:
				return $this->tpl_assemble(
					'Service Request Form',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Company', 'company' ),
						$this->tpl_choice( 'select', 'Service Needed', 'service', array( 'Web Design', 'Development', 'SEO', 'Branding', 'Consulting' ) ),
						$this->tpl_choice( 'select', 'Budget Range', 'budget', array( 'Under $1,000', '$1,000 – $5,000', '$5,000 – $10,000', '$10,000+' ) ),
						$this->tpl_text( 'Desired Start Date', 'start_date', 'date' ),
						$this->tpl_textarea( 'Project Requirements', 'requirements', 'Describe your project…', 5 ),
					),
					array(
						array(
							'label'  => 'Project Deposit',
							'amount' => 100,
						),
					),
					'Request Quote'
				);

			// ── Booking ──────────────────────────────────────────────
			case 7001:
				return $this->tpl_assemble(
					'Appointment Booking',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_choice( 'select', 'Service', 'service', array( 'Consultation', 'Checkup', 'Treatment', 'Follow-up' ) ),
						$this->tpl_text( 'Preferred Date', 'appt_date', 'date' ),
						$this->tpl_choice( 'select', 'Preferred Time', 'appt_time', array( 'Morning', 'Afternoon', 'Evening' ) ),
						$this->tpl_textarea( 'Notes', 'notes', 'Anything we should prepare for?', 3 ),
					),
					array(
						array(
							'label'  => 'Standard (30 min)',
							'amount' => 40,
						),
						array(
							'label'  => 'Extended (60 min)',
							'amount' => 80,
						),
					),
					'Book Appointment'
				);

			case 7002:
				return $this->tpl_assemble(
					'Table Reservation',
					array(
						$this->tpl_name(),
						$this->tpl_email(),
						$this->tpl_text( 'Phone', 'phone', 'tel', '+1 (555) 000-0000' ),
						$this->tpl_text( 'Reservation Date', 'res_date', 'date' ),
						$this->tpl_text( 'Party Size', 'party_size', 'number', '2' ),
						$this->tpl_choice( 'radio', 'Seating Preference', 'seating', array( 'Indoor', 'Outdoor', 'Bar', 'No preference' ), 'no-preference' ),
						$this->tpl_textarea( 'Special Requests', 'requests', 'Allergies, occasions, accessibility…', 3 ),
					),
					array(
						array(
							'label'  => 'Reservation Deposit',
							'amount' => 20,
						),
					),
					'Reserve Table'
				);

			default:
				return null;
		}
	}

	/**
	 * Pass through the preview URL — WP's built-in CPT preview mechanism works as-is.
	 *
	 * @param string   $url
	 * @param \WP_Post $post
	 * @return string
	 */
	public function filter_preview_url( string $url, \WP_Post $post ): string {
		if ( 'smartpay_form' !== $post->post_type ) {
			return $url;
		}
		return $url;
	}

	/**
	 * Serve a standalone template for all smartpay_form frontend requests.
	 *
	 * @param string $template
	 * @return string
	 */
	public function serve_preview_template( string $template ): string {
		if ( ! is_singular( 'smartpay_form' ) ) {
			return $template;
		}

		$form_template = plugin_dir_path( SMARTPAY_PLUGIN_FILE ) . 'resources/views/smartpay-form-preview.php';

		return file_exists( $form_template ) ? $form_template : $template;
	}

	/**
	 * Add a Shortcode column to the smartpay_form CPT list table.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_shortcode_column( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['sp_shortcode'] = __( 'Shortcode', 'smartpay' );
			}
		}
		return $new;
	}

	/**
	 * Render the Shortcode column value.
	 *
	 * @param string $column
	 * @param int    $post_id
	 */
	public function render_shortcode_column( string $column, int $post_id ): void {
		if ( 'sp_shortcode' !== $column ) {
			return;
		}
		$code = '[sp_form id="' . absint( $post_id ) . '"]';
		printf(
			'<code class="sp-shortcode-chip" data-code="%1$s" style="cursor:pointer;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:4px;padding:2px 8px;font-size:12px;" title="%2$s">%3$s</code>',
			esc_attr( $code ),
			esc_attr__( 'Click to copy', 'smartpay' ),
			esc_html( $code )
		);
	}

	/**
	 * Inline JS: click-to-copy for shortcode chips in the CPT list table.
	 */
	public function shortcode_column_script(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-smartpay_form' !== $screen->id ) {
			return;
		}
		?>
		<script>
		(function(){
			document.addEventListener('click', function(e){
				var chip = e.target.closest('.sp-shortcode-chip');
				if (!chip) return;
				var code = chip.getAttribute('data-code');
				if (!code) return;
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(code).then(function(){
						var orig = chip.textContent;
						chip.textContent = '<?php echo esc_js( __( 'Copied!', 'smartpay' ) ); ?>';
						setTimeout(function(){ chip.textContent = orig; }, 1500);
					});
				} else {
					var ta = document.createElement('textarea');
					ta.value = code;
					ta.style.position = 'fixed';
					ta.style.opacity = '0';
					document.body.appendChild(ta);
					ta.select();
					document.execCommand('copy');
					document.body.removeChild(ta);
					var orig = chip.textContent;
					chip.textContent = '<?php echo esc_js( __( 'Copied!', 'smartpay' ) ); ?>';
					setTimeout(function(){ chip.textContent = orig; }, 1500);
				}
			});
		}());
		</script>
		<?php
	}

	/**
	 * Fix payment data for CPT forms.
	 *
	 * The free plugin's Form model queries `wp_smartpay_forms`; CPT forms live in
	 * `wp_posts`. This filter rebuilds payment data from raw POST before gateway processing.
	 *
	 * @param array $data Prepared payment data (may be broken for CPT forms).
	 * @param array $raw  Raw sanitized POST data.
	 * @return array
	 */
	public function fix_cpt_form_payment_data( array $data, array $raw ): array {
		if ( ( $data['payment_type'] ?? '' ) !== 'form_payment' ) {
			return $data;
		}

		$form_id = absint( $raw['smartpay_form_id'] ?? 0 );
		if ( ! $form_id ) {
			return $data;
		}

		$post = get_post( $form_id );
		if ( ! $post || 'smartpay_form' !== $post->post_type ) {
			return $data;
		}

		$amount       = (float) ( $raw['smartpay_amount'] ?? 0 );
		$billing_type = sanitize_text_field( $raw['smartpay_form_billing_type'] ?? 'One Time' );

		$data['payment_data'] = array(
			'form_id'          => $form_id,
			'total_amount'     => $amount,
			'billing_type'     => $billing_type,
			'is_custom_amount' => filter_var(
				$raw['smartpay_is_custom_amount'] ?? false,
				FILTER_VALIDATE_BOOLEAN
			),
		);
		$data['amount']       = $amount;

		return $data;
	}

	/**
	 * JSON-decode a meta string, returning $default on failure.
	 *
	 * @param mixed $value
	 * @param mixed $default
	 * @return mixed
	 */
	private function decode_meta_json( $value, $default ) {
		if ( ! $value ) {
			return $default;
		}
		$decoded = json_decode( $value, true );
		return ( JSON_ERROR_NONE === json_last_error() && null !== $decoded ) ? $decoded : $default;
	}

	/**
	 * Build block editor settings for the form editor page.
	 *
	 * @return array
	 */
	private function get_editor_settings(): array {
		$settings = array(
			'disableCustomColors'         => get_theme_support( 'disable-custom-colors' ),
			'disableCustomFontSizes'      => get_theme_support( 'disable-custom-font-sizes' ),
			'isRTL'                       => is_rtl(),
			'__experimentalBlockPatterns' => array(),
		);

		list( $color_palette, ) = (array) get_theme_support( 'editor-color-palette' );
		list( $font_sizes, )    = (array) get_theme_support( 'editor-font-sizes' );

		if ( false !== $color_palette ) {
			$settings['colors'] = $color_palette;
		}
		if ( false !== $font_sizes ) {
			$settings['fontSizes'] = $font_sizes;
		}

		return $settings;
	}

	/**
	 * JS snippet that re-registers all `smartpay-form/*` blocks under the
	 * `wp-smartpay` block category for proper inserter grouping.
	 *
	 * @return string
	 */
	public static function get_block_category_script(): string {
		return '(function(){
			if ( ! wp || ! wp.domReady || ! wp.blocks ) { return; }
			wp.domReady( function () {
				var blockMeta = {
					"smartpay-form/name": {
						description: "Collect the customer\'s full name.",
						example: { attributes: { label: "Full Name", placeholder: "Your name" } }
					},
					"smartpay-form/customer-email": {
						description: "Collect the customer\'s email address.",
						example: { attributes: { label: "Email Address", placeholder: "you@example.com" } }
					},
					"smartpay-form/text-input": {
						description: "A single-line text input field.",
						example: { attributes: { label: "Message", placeholder: "Type here…" } }
					},
					"smartpay-form/textarea": {
						description: "A multi-line text area field.",
						example: { attributes: { label: "Notes", placeholder: "Additional notes…" } }
					},
					"smartpay-form/radio": {
						description: "A radio button group for single-choice selection.",
						example: { attributes: { label: "Preference", options: ["Option A", "Option B"] } }
					},
					"smartpay-form/address": {
						description: "Collect a full postal address.",
						example: { attributes: { label: "Address" } }
					},
					"smartpay-form/checkbox": {
						description: "A single checkbox for opt-in or agreement.",
						example: { attributes: { label: "I agree to the terms" } }
					},
					"smartpay-form/select": {
						description: "A dropdown selection field.",
						example: { attributes: { label: "Country", options: ["US", "UK"] } }
					}
				};

				var blockSupports = {
					color: { text: true, background: true },
					spacing: { margin: true, padding: true },
					typography: { fontSize: true, lineHeight: true, __experimentalFontStyle: true, __experimentalFontWeight: true }
				};

				wp.blocks.getBlockTypes().forEach( function ( block ) {
					if ( block.name.indexOf( "smartpay-form/" ) !== 0 ) { return; }
					var meta = blockMeta[ block.name ] || {};
					wp.blocks.unregisterBlockType( block.name );
					wp.blocks.registerBlockType(
						block.name,
						Object.assign( {}, block, {
							category: "wp-smartpay",
							supports: Object.assign( {}, block.supports || {}, blockSupports ),
							example: meta.example || block.example,
							description: meta.description || block.description
						} )
					);
				} );
			} );
		}());';
	}
}
