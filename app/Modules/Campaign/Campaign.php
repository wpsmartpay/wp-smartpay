<?php
/**
 * Campaigns module.
 *
 * @package SmartPay
 */

namespace SmartPay\Modules\Campaign;

defined( 'ABSPATH' ) || exit;

use SmartPay\Http\Controllers\Rest\Admin\CampaignController;
use WP_REST_Server;

/**
 * Campaigns: a taxonomy on the `smartpay_form` post type that groups forms
 * under one goal, plus the public campaign page, progress shortcode/block and
 * the admin REST API.
 *
 * No new table and no change to payments — see app/Helpers/campaign.php.
 *
 * @package SmartPay\Modules\Campaign
 */
class Campaign {

	/**
	 * Bump when the taxonomy rewrite changes so rules are flushed once.
	 */
	const REWRITE_VERSION = '1';

	/**
	 * Application container.
	 *
	 * @var mixed
	 */
	protected $app;

	/**
	 * Re-entrancy guard for the one-campaign-per-form rule.
	 *
	 * @var bool
	 */
	private $enforcing = false;

	/**
	 * Hook the module in.
	 *
	 * @param mixed $app Application container.
	 */
	public function __construct( $app ) {
		$this->app = $app;

		add_action( 'init', array( $this, 'register_taxonomy' ), 5 );
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 99 );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'set_object_terms', array( $this, 'enforce_single_campaign' ), 10, 6 );
		add_filter( 'template_include', array( $this, 'campaign_template' ) );
		add_filter( 'smartpay_needs_frontend_assets', array( $this, 'campaign_page_needs_assets' ) );
	}

	/**
	 * The campaign page embeds a form, so it needs the frontend bundle.
	 *
	 * @param bool $needs Whether assets are already needed.
	 * @return bool
	 */
	public function campaign_page_needs_assets( $needs ): bool {
		return $needs || is_tax( SMARTPAY_CAMPAIGN_TAXONOMY );
	}

	/**
	 * Register the `smartpay_campaign` taxonomy and its term meta.
	 */
	public function register_taxonomy(): void {
		register_taxonomy(
			SMARTPAY_CAMPAIGN_TAXONOMY,
			'smartpay_form',
			array(
				'labels'             => array(
					'name'          => __( 'Campaigns', 'smartpay' ),
					'singular_name' => __( 'Campaign', 'smartpay' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'hierarchical'       => false,
				// Managed from the SmartPay SPA, never from the core term screens.
				'show_ui'            => false,
				'show_in_menu'       => false,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'show_admin_column'  => false,
				// REST so the form editor can assign a campaign with the post save.
				'show_in_rest'       => true,
				'rest_base'          => 'smartpay_campaigns',
				'query_var'          => true,
				'rewrite'            => array(
					'slug'       => 'campaign',
					'with_front' => false,
				),
				'capabilities'       => array(
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				),
			)
		);

		$meta = array(
			'smartpay_campaign_story'         => 'string',
			'smartpay_campaign_cover_id'      => 'integer',
			'smartpay_campaign_goal_type'     => 'string',
			'smartpay_campaign_goal_target'   => 'number',
			'smartpay_campaign_end_date'      => 'string',
			'smartpay_campaign_goal_behavior' => 'string',
			'smartpay_campaign_default_form'  => 'integer',
		);

		foreach ( $meta as $key => $type ) {
			register_term_meta(
				SMARTPAY_CAMPAIGN_TAXONOMY,
				$key,
				array(
					'type'         => $type,
					'single'       => true,
					'show_in_rest' => false,
				)
			);
		}

		register_post_meta(
			'smartpay_form',
			'_smartpay_is_donation_form',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'default'       => false,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Flush rewrite rules once so /campaign/{slug} resolves on existing installs.
	 */
	public function maybe_flush_rewrite_rules(): void {
		if ( get_option( 'smartpay_campaign_rewrite_version' ) === self::REWRITE_VERSION ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'smartpay_campaign_rewrite_version', self::REWRITE_VERSION );
	}

	/**
	 * A form belongs to one campaign or none: keep only the newest term.
	 *
	 * Also drops the stale campaign's cached totals and its default-form
	 * pointer when this form was that default.
	 *
	 * @param int    $object_id  Form post ID.
	 * @param array  $terms      Terms passed in.
	 * @param array  $tt_ids     New term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy.
	 * @param bool   $append     Append mode.
	 * @param array  $old_tt_ids Previous term taxonomy IDs.
	 */
	public function enforce_single_campaign( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ): void {
		if ( SMARTPAY_CAMPAIGN_TAXONOMY !== $taxonomy || $this->enforcing ) {
			return;
		}

		$tt_ids = array_map( 'intval', (array) $tt_ids );

		if ( count( $tt_ids ) > 1 ) {
			$this->enforcing = true;
			$keep            = get_term_by( 'term_taxonomy_id', end( $tt_ids ), SMARTPAY_CAMPAIGN_TAXONOMY );
			wp_set_object_terms( (int) $object_id, $keep ? array( (int) $keep->term_id ) : array(), SMARTPAY_CAMPAIGN_TAXONOMY );
			$this->enforcing = false;
		}

		foreach ( array_unique( array_merge( $tt_ids, array_map( 'intval', (array) $old_tt_ids ) ) ) as $tt_id ) {
			$term = get_term_by( 'term_taxonomy_id', $tt_id, SMARTPAY_CAMPAIGN_TAXONOMY );
			if ( ! $term ) {
				continue;
			}

			smartpay_invalidate_campaign_cache( (int) $term->term_id );

			$still_attached = has_term( (int) $term->term_id, SMARTPAY_CAMPAIGN_TAXONOMY, (int) $object_id );
			if ( ! $still_attached && (int) get_term_meta( $term->term_id, 'smartpay_campaign_default_form', true ) === (int) $object_id ) {
				delete_term_meta( $term->term_id, 'smartpay_campaign_default_form' );
			}
		}

		// The form's own goal is paused/resumed, so its cached value is stale too.
		delete_transient( "smartpay_goal_{$object_id}_quantity" );
		delete_transient( "smartpay_goal_{$object_id}_amount" );
	}

	/**
	 * Serve the plugin's campaign page for /campaign/{slug}.
	 *
	 * @param string $template Template chosen by WordPress.
	 * @return string
	 */
	public function campaign_template( string $template ): string {
		if ( ! is_tax( SMARTPAY_CAMPAIGN_TAXONOMY ) ) {
			return $template;
		}

		$campaign_template = SMARTPAY_DIR . 'resources/views/campaign/page.php';

		return file_exists( $campaign_template ) ? $campaign_template : $template;
	}

	/**
	 * Register `[smartpay_campaign_progress id=""]`.
	 */
	public function register_shortcodes(): void {
		add_shortcode( 'smartpay_campaign_progress', array( $this, 'render_progress_shortcode' ) );
	}

	/**
	 * Render the campaign progress bar.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_progress_shortcode( $atts ): string {
		$atts     = shortcode_atts( array( 'id' => 0 ), $atts, 'smartpay_campaign_progress' );
		$campaign = smartpay_get_campaign( absint( $atts['id'] ) );

		if ( ! $campaign ) {
			return '';
		}

		wp_enqueue_style( 'smartpay-campaign' );

		return (string) smartpay_view(
			'campaign.progress',
			array(
				'campaign' => $campaign,
				'progress' => smartpay_calculate_campaign_progress( $campaign['id'] ),
			)
		);
	}

	/**
	 * Register the campaign progress block and the shared campaign stylesheet.
	 */
	public function register_block(): void {
		wp_register_style(
			'smartpay-campaign',
			SMARTPAY_PLUGIN_ASSETS . '/css/frontend/campaign.css',
			array(),
			SMARTPAY_VERSION
		);

		if ( ! file_exists( SMARTPAY_DIR . 'public/blocks/campaign-progress/block.json' ) ) {
			return;
		}

		register_block_type(
			SMARTPAY_DIR . 'public/blocks/campaign-progress',
			array(
				'render_callback' => function ( array $attributes ): string {
					$id = absint( $attributes['id'] ?? 0 );
					return $id ? $this->render_progress_shortcode( array( 'id' => $id ) ) : '';
				},
			)
		);
	}

	/**
	 * Admin REST API for campaigns.
	 */
	public function register_rest_routes(): void {
		$controller = $this->app->make( CampaignController::class );
		$auth       = array( $controller, 'middleware' );

		register_rest_route(
			'smartpay/v1',
			'campaigns',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $controller, 'index' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $controller, 'store' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			'smartpay/v1',
			'campaigns/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $controller, 'show' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => 'PUT, PATCH',
					'callback'            => array( $controller, 'update' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $controller, 'destroy' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			'smartpay/v1',
			'campaigns/(?P<id>[\d]+)/overview',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $controller, 'overview' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			'smartpay/v1',
			'campaigns/(?P<id>[\d]+)/forms',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $controller, 'forms' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $controller, 'attach_forms' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			'smartpay/v1',
			'campaigns/(?P<id>[\d]+)/forms/(?P<form_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $controller, 'detach_form' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			'smartpay/v1',
			'campaigns/(?P<id>[\d]+)/default-form',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $controller, 'set_default_form' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			'smartpay/v1',
			'campaigns/(?P<id>[\d]+)/donors',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $controller, 'donors' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			'smartpay/v1',
			'native-forms/campaign',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $controller, 'assign_forms' ),
				'permission_callback' => $auth,
			)
		);
	}
}
