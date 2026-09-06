<?php

namespace SmartPay\Modules\Shortcodes;

defined( 'ABSPATH' ) || exit;

class UserProfileShortcode {
	public static function register() {
		add_shortcode( 'smartpay_user_profile', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueueAssets' ) );
	}

	public static function render() {
		wp_enqueue_style( 'smartpay-user-profile-frontend' );
		wp_enqueue_script( 'smartpay-user-profile-frontend' );

		ob_start();
		self::load_view();
		return ob_get_clean();
	}

	public static function enqueueAssets() {
		wp_register_style(
			'smartpay-user-profile-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/css/frontend/profile.css',
			array(),
			SMARTPAY_VERSION
		);

		wp_register_script(
			'smartpay-user-profile-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/js/frontend/profile.js',
			array(),
			SMARTPAY_VERSION,
			true
		);

		$post = get_post();
		if ( $post && has_shortcode( $post->post_content, 'smartpay_user_profile' ) ) {
			wp_enqueue_style( 'smartpay-user-profile-frontend' );
			wp_enqueue_script( 'smartpay-user-profile-frontend' );

			wp_localize_script(
				'smartpay-user-profile-frontend',
				'smartpayData',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'smartpay_frontend_nonce' ),
					'strings' => array(
						'processing' => __( 'Processing...', 'smartpay' ),
						'error'      => __( 'An error occurred. Please try again.', 'smartpay' ),
					),
				)
			);
		}
	}

	private static function load_view() {
		include SMARTPAY_DIR . 'resources/views/shortcodes/profile.php';
	}
}
