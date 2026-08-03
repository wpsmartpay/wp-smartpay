<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

class UserDashboard {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'the_content', array( $this, 'inject_shortcode' ) );
	}

	/**
	 * Enqueue dashboard styles on the dashboard page.
	 *
	 * Must run on wp_enqueue_scripts (before wp_head) — enqueuing inside the
	 * shortcode callback is too late, the <link> never reaches <head> and the
	 * full-width flex layout fails to apply.
	 */
	public function enqueue_assets() {
		if ( ! $this->is_smartpay_dashboard_page() ) {
			return;
		}

		wp_enqueue_style(
			'smartpay-user-dashboard-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/css/frontend/dashboard.css',
			array(),
			SMARTPAY_VERSION
		);
	}

	public function inject_shortcode( $content ) {
		if ( $this->is_smartpay_dashboard_page() && in_the_loop() && is_main_query() ) {
			return do_shortcode( '[smartpay_dashboard]' );
		}
		return $content;
	}

	public function maybe_redirect() {
		if ( ! $this->is_smartpay_dashboard_page() ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			$settings = get_option( 'smartpay_settings', array() );
			$page_id  = (int) ( $settings['user_login_page'] ?? 0 );
			wp_safe_redirect( $page_id ? get_permalink( $page_id ) : home_url() );
			exit;
		}
	}

	protected function is_smartpay_dashboard_page() {
		$settings = get_option( 'smartpay_settings', array() );
		return ! empty( $settings['customer_dashboard_page'] ) && is_page( (int) $settings['customer_dashboard_page'] );
	}
}
