<?php

namespace SmartPay\Modules\Shortcodes;

defined( 'ABSPATH' ) || exit;

class RegisterShortcode {

	public function __construct() {
		// Dashboard renders via the core [smartpay_dashboard] shortcode (Modules\Shortcode\Shortcode).
		CustomerLoginShortcode::register();
		UserProfileShortcode::register();
		UserRegistrationShortcode::register();

		// Register a nav-menu location so the dashboard sidebar is customizable
		// from Appearance → Menus. When no menu is assigned, the sidebar falls
		// back to its built-in items.
		add_action(
			'init',
			static function () {
				register_nav_menu( 'smartpay_dashboard_sidebar', __( 'SmartPay Dashboard Sidebar', 'smartpay' ) );
			}
		);
	}
}
