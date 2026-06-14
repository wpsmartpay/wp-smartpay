<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

class CreatePages {

	public function __construct() {
		// Dashboard page is created by the core Activator (customer_dashboard_page).
		$this->create_login_page();
		$this->create_registration_page();
		$this->create_profile_page();
	}

	public function create_login_page() {
		$smartpay_settings = get_option( 'smartpay_settings', array() );

		// Setup Login page
		$user_login_page = array_key_exists( 'user_login_page', $smartpay_settings ) ? $smartpay_settings['user_login_page'] : false;

		if ( empty( $user_login_page ) ) {
			$user_login_page = \wp_insert_post(
				array(
					'post_title'     => __( 'Login', 'smartpay' ),
					'post_name'      => 'smartpay-user-login',
					'post_content'   => sprintf( '<!-- wp:shortcode -->%s<!-- /wp:shortcode -->', '[smartpay_user_login]' ),
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);
		}

		$options = array(
			'user_login_page' => $user_login_page,
		);

		update_option( 'smartpay_settings', array_merge( $smartpay_settings, $options ) );
	}

	public function create_registration_page() {
		$smartpay_settings = get_option( 'smartpay_settings', array() );

		// Setup Login page
		$user_registration_page = array_key_exists( 'user_registration_page', $smartpay_settings ) ? $smartpay_settings['user_registration_page'] : false;

		if ( empty( $user_registration_page ) ) {
			$user_registration_page = \wp_insert_post(
				array(
					'post_title'     => __( 'Register', 'smartpay' ),
					'post_name'      => 'smartpay-user-registration',
					'post_content'   => sprintf( '<!-- wp:shortcode -->%s<!-- /wp:shortcode -->', '[smartpay_user_registration]' ),
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);
		}

		$options = array(
			'user_registration_page' => $user_registration_page,
		);

		update_option( 'smartpay_settings', array_merge( $smartpay_settings, $options ) );
	}

	public function create_profile_page() {
		$smartpay_settings = get_option( 'smartpay_settings', array() );

		// Setup profile page
		$user_profile_page = array_key_exists( 'user_profile_page', $smartpay_settings ) ? $smartpay_settings['user_profile_page'] : false;

		if ( empty( $user_profile_page ) ) {
			$user_profile_page = \wp_insert_post(
				array(
					'post_title'     => __( 'Profile', 'smartpay' ),
					'post_name'      => 'smartpay-user-profile',
					'post_content'   => sprintf( '<!-- wp:shortcode -->%s<!-- /wp:shortcode -->', '[smartpay_user_profile]' ),
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);
		}

		$options = array(
			'user_profile_page' => $user_profile_page,
		);

		update_option( 'smartpay_settings', array_merge( $smartpay_settings, $options ) );
	}
}
