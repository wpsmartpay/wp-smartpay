<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

class UserDashboard {

	public function __construct() {
		add_filter( 'template_include', array( $this, 'render_layout' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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

	public function render_layout( $template ) {
		if ( $this->is_smartpay_dashboard_page() ) {
			if ( ! is_user_logged_in() ) {
				$settings = get_option( 'smartpay_settings', array() );
				$page_id  = (int) ( $settings['user_login_page'] ?? 0 );
				if ( $page_id ) {
					wp_safe_redirect( get_permalink( $page_id ) );
				} else {
					wp_safe_redirect( home_url() );
				}
			} elseif ( ! smartpay_is_customer() ) {
				wp_safe_redirect( home_url() );
			}
			$shortcode = 'smartpay_dashboard';
			include SMARTPAY_DIR . 'resources/views/templates/layout.php';
			return;
		}

		return $template;
	}

	protected function is_smartpay_dashboard_page() {
		$settings = get_option( 'smartpay_settings', array() );
		return ! empty( $settings['customer_dashboard_page'] ) && is_page( (int) $settings['customer_dashboard_page'] );
	}
}
