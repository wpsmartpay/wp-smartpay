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
		$sidebar_js  = SMARTPAY_PLUGIN_ASSETS . '/js/admin/form-editor-sidebar.js';
		$sidebar_css = SMARTPAY_PLUGIN_ASSETS . '/css/admin/form-editor-sidebar.css';

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

		if ( file_exists( $sidebar_css ) ) {
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
	 * @param int $template_id Template ID from the UI template library.
	 */
	private function inject_template_blocks( int $template_id ): void {
		$template = $this->get_template_definition( $template_id );
		if ( ! $template ) {
			return;
		}

		$field_to_block = array(
			'name'     => 'smartpay-form/name',
			'email'    => 'smartpay-form/email',
			'text'     => 'smartpay-form/text-input',
			'textarea' => 'smartpay-form/textarea-input',
			'select'   => 'smartpay-form/select-input',
			'radio'    => 'smartpay-form/radio-input',
			'checkbox' => 'smartpay-form/checkbox-input',
			'address'  => 'smartpay-form/address-input',
		);

		$blocks = array();
		foreach ( $template['fields'] as $field ) {
			if ( 'submit' === $field ) {
				continue;
			}
			if ( isset( $field_to_block[ $field ] ) ) {
				$blocks[] = array(
					'name'  => $field_to_block[ $field ],
					'attrs' => new \stdClass(),
				);
			}
		}

		$meta = array(
			'amounts' => array(
				array(
					'key'          => 'default',
					'label'        => '',
					'amount'       => '0.00',
					'billing_type' => 'One Time',
				),
			),
		);

		wp_add_inline_script(
			'smartpay-form-editor-sidebar',
			'window.spTemplateBlocks = ' . wp_json_encode( $blocks ) . ';' .
			'window.spTemplateMeta = ' . wp_json_encode( $meta ) . ';',
			'before'
		);
	}

	/**
	 * Return a template definition by ID, or null if not found.
	 *
	 * @param int $id
	 * @return array|null
	 */
	private function get_template_definition( int $id ): ?array {
		$templates = array(
			1001 => array( 'name' => 'Simple Payment Form',         'fields' => array( 'name', 'email', 'submit' ) ),
			1002 => array( 'name' => 'Product Order Form',          'fields' => array( 'name', 'email', 'text', 'text', 'textarea', 'submit' ) ),
			1003 => array( 'name' => 'Invoice Payment Form',        'fields' => array( 'name', 'email', 'text', 'textarea', 'submit' ) ),
			1004 => array( 'name' => 'Subscription Signup',         'fields' => array( 'name', 'email', 'select', 'textarea', 'submit' ) ),
			2001 => array( 'name' => 'Simple Donation Form',        'fields' => array( 'name', 'email', 'select', 'radio', 'checkbox', 'textarea', 'submit' ) ),
			2002 => array( 'name' => 'Charity Donation',            'fields' => array( 'name', 'email', 'text', 'select', 'radio', 'textarea', 'submit' ) ),
			2003 => array( 'name' => 'Nonprofit Donation',          'fields' => array( 'name', 'email', 'address', 'select', 'checkbox', 'submit' ) ),
			3001 => array( 'name' => 'Event Registration',          'fields' => array( 'name', 'email', 'text', 'select', 'text', 'submit' ) ),
			3002 => array( 'name' => 'Newsletter Signup',           'fields' => array( 'name', 'email', 'select', 'checkbox', 'submit' ) ),
			3003 => array( 'name' => 'Course Enrollment',           'fields' => array( 'name', 'email', 'text', 'select', 'select', 'textarea', 'submit' ) ),
			3004 => array( 'name' => 'Membership Application',      'fields' => array( 'name', 'email', 'text', 'select', 'address', 'textarea', 'submit' ) ),
			4001 => array( 'name' => 'Conference Registration',     'fields' => array( 'name', 'email', 'text', 'text', 'select', 'radio', 'submit' ) ),
			4002 => array( 'name' => 'Workshop Registration',       'fields' => array( 'name', 'email', 'select', 'text', 'checkbox', 'submit' ) ),
			4003 => array( 'name' => 'Webinar Registration',        'fields' => array( 'name', 'email', 'text', 'select', 'checkbox', 'submit' ) ),
			5001 => array( 'name' => 'Customer Satisfaction Survey','fields' => array( 'name', 'email', 'radio', 'radio', 'textarea', 'submit' ) ),
			5002 => array( 'name' => 'Product Feedback Form',       'fields' => array( 'name', 'email', 'radio', 'radio', 'checkbox', 'textarea', 'submit' ) ),
			6001 => array( 'name' => 'Contact & Payment Form',      'fields' => array( 'name', 'email', 'text', 'radio', 'textarea', 'submit' ) ),
			6002 => array( 'name' => 'Service Request Form',        'fields' => array( 'name', 'email', 'select', 'text', 'text', 'textarea', 'submit' ) ),
		);

		return $templates[ $id ] ?? null;
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
		$data['amount'] = $amount;

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
					typography: { fontSize: true }
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
