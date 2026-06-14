<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

class UserDashboard {

	public function __construct() {
		add_filter( 'template_include', array( $this, 'render_layout' ) );
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
			} elseif ( ! is_smartpay_customer() ) {
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
