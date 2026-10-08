<?php

namespace SmartPay\Modules\Shortcodes;

defined( 'ABSPATH' ) || exit;

class UserRegistrationShortcode {
	public static function register() {
		add_shortcode( 'smartpay_user_registration', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueueAssets' ) );
	}

	public static function render() {
		wp_enqueue_style( 'smartpay-registration-frontend' );
		wp_enqueue_script( 'smartpay-registration-frontend' );

		ob_start();
		self::load_view();
		return ob_get_clean();
	}

	public static function enqueueAssets() {
		wp_register_style(
			'smartpay-registration-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/css/frontend/registration.css',
			array(),
			SMARTPAY_VERSION
		);

		wp_register_script(
			'smartpay-registration-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/js/frontend/registration.js',
			array(),
			SMARTPAY_VERSION,
			true
		);

		$post = get_post();
		if ( $post && has_shortcode( $post->post_content, 'smartpay_user_registration' ) ) {
			wp_enqueue_style( 'smartpay-registration-frontend' );
			wp_enqueue_script( 'smartpay-registration-frontend' );

			wp_localize_script(
				'smartpay-registration-frontend',
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
		include SMARTPAY_DIR . 'resources/views/shortcodes/registration.php';
	}
}
