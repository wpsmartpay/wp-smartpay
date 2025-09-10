<?php

namespace SmartPay;

use SmartPay\Modules\Admin\Utilities\Upload;

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Activator {
	public $upload;
	/**
	 * if any changes/updates on database
	 * update the database version
	 * @var $db_version
	 */
	private const SMARTPAY_DB_VERSION = '2.0';

	public function __construct() {
		$this->migrate();
		$this->_create_pages();
		$this->_set_default_settings();

		smartpay()->make( Upload::class )->protectDirectory( true );
		self::activatePlugin();
	}

	public static function boot() {
		return new self();
	}

	public function migrate() {
		\CreateSmartpayProductsTable::up();
		\CreateSmartpayFormsTable::up();
		\CreateSmartpayCouponsTable::up();
		\CreateSmartpayCustomersTable::up();
		\CreateSmartpayPaymentsTable::up();
	}

	/**
	 * Create necessary pages.
	 *
	 * @return void
	 * @since 0.0.1
	 * @access private
	 */
	private function _create_pages() {
		$smartpay_settings = get_option( 'smartpay_settings', [] );

		// Setup payment success page
		$payment_success_page = array_key_exists( 'payment_success_page', $smartpay_settings ) ? $smartpay_settings['payment_success_page'] : false;

		if ( empty( $payment_success_page ) ) {
			$payment_success_page = \wp_insert_post(
				array(
					'post_title'     => __( 'Payment Confirmation', 'smartpay' ),
					'post_name'      => 'smartpay-payment-confirmation',
					'post_content'   => "<!-- wp:shortcode -->[smartpay_payment_receipt]<!-- /wp:shortcode -->",
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);
		}

		// Setup payment failure page
		$payment_failure_page = array_key_exists( 'payment_failure_page', $smartpay_settings ) ? $smartpay_settings['payment_failure_page'] : false;

		if ( empty( $payment_failure_page ) ) {
			$payment_failure_page = \wp_insert_post(
				array(
					'post_title'     => __( 'Payment Failed', 'smartpay' ),
					'post_name'      => 'smartpay-payment-failed',
					'post_content'   => __( '<!-- wp:paragraph --><p>We\'re sorry, but your transaction failed to process. Please try again or contact site support.</p><!-- /wp:paragraph -->', 'smartpay' ),
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);
		}

		// Setup customer dashboard page
		$customer_dashboard_page = array_key_exists( 'customer_dashboard_page', $smartpay_settings ) ? $smartpay_settings['customer_dashboard_page'] : false;

		if ( empty( $customer_dashboard_page ) ) {
			$customer_dashboard_page = \wp_insert_post(
				array(
					'post_title'     => __( 'Dashboard', 'smartpay' ),
					'post_name'      => 'smartpay-customer-dashboard',
					'post_content'   => sprintf( "<!-- wp:shortcode -->%s<!-- /wp:shortcode -->", '[smartpay_dashboard]' ),
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);
		}

		$options = array(
			// 'payment_page'            => $payment_page,
			'payment_success_page'    => $payment_success_page,
			'payment_failure_page'    => $payment_failure_page,
			'customer_dashboard_page' => $customer_dashboard_page,
		);

		update_option( 'smartpay_settings', array_merge( $smartpay_settings, $options ) );
	}

	/**
	 * Set default settings.
	 *
	 * @return void
	 * @since 0.0.2
	 * @access private
	 */
	private function _set_default_settings() {
		$smartpay_settings = get_option( 'smartpay_settings', [] );

		$options = array(
			// General
			'currency'        => 'USD',

			// Gateway
			'test_mode'       => 0,
			'gateways'        => [ 'paypal' => 1 ],
			'default_gateway' => 'paypal',

			// Email
			'from_name'       => get_bloginfo( 'name' ),
			'from_email'      => get_bloginfo( 'admin_email' ),

			'payment_email_subject' => 'Payment Receipt - ' . get_bloginfo( 'name' ),
			'payment_email_heading' => 'Payment Receipt - ' . get_bloginfo( 'name' ),

			'integrations' => [],
		);

		update_option( 'smartpay_settings', array_merge( $smartpay_settings, $options ) );

		// set the db version to option table
		if ( is_null( get_option( 'smartpay_db_version' ) ) ) {
			add_option( 'smartpay_db_version', self::SMARTPAY_DB_VERSION );
		} else {
			update_option( 'smartpay_db_version', self::SMARTPAY_DB_VERSION );
		}

	}

	public static function activatePlugin() {
		// phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) {
			return;
		}

		// Add transient to trigger redirect to the Welcome screen.
		set_transient( 'wpsmartpay_activation_redirect', true, 30 );
	}
}