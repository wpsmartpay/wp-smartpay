<?php

namespace SmartPay\Modules\Role;

defined( 'ABSPATH' ) || exit;

class Roles {

	public function __construct() {
		add_action( 'init', [ $this, 'add_roles' ] );
		$this->restrict_access();
	}

	public function add_roles() {
		if ( ! get_role( 'smartpay_customer' ) ) {
			add_role(
				'smartpay_customer',
				__( 'SmartPay Customer', 'smartpay' ),
				array(
					'read'                      => true,
					'access_smartpay_dashboard' => true,
					'deny_wp_admin'             => true,
				)
			);
		}
	}

	public function restrict_access() {
		add_action(
			'admin_init',
			function () {
				if ( ! is_user_logged_in() || wp_doing_ajax() ) {
					return;
				}

				if ( current_user_can( 'edit_posts' ) ) {
					return;
				}

				if ( current_user_can( 'access_smartpay_dashboard' ) ) {
					$settings = get_option( 'smartpay_settings', array() );
					$page_id  = (int) ( $settings['customer_dashboard_page'] ?? 0 );

					if ( $page_id ) {
						wp_safe_redirect( get_permalink( $page_id ) );
						exit;
					}
				}
			}
		);

		add_filter(
			'show_admin_bar',
			function ( $show ) {
				if ( current_user_can( 'access_smartpay_dashboard' ) && current_user_can( 'deny_wp_admin' ) && ! current_user_can( 'edit_posts' ) ) {
					return false;
				}
				return $show;
			}
		);
	}
}
