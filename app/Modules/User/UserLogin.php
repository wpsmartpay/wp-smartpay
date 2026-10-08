<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

class UserLogin {

	public function __construct() {
		add_action( 'wp_ajax_nopriv_smartpay_user_login', array( $this, 'handle_user_login' ) );
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'the_content', array( $this, 'inject_shortcode' ) );
		add_action( 'wp_head', array( $this, 'hide_page_title' ) );
	}

	public function handle_user_login() {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}

		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are already logged in.', 'smartpay' ),
				)
			);
		}

		if ( $this->is_login_rate_limited() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Too many failed login attempts. Please try again later.', 'smartpay' ),
				)
			);
		}

		$user_login = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		// Passwords are deliberately not sanitized: sanitize_text_field() strips
		// tag-like sequences and trims whitespace, which would silently alter a
		// legitimate password and reject a correct login. The value is only ever
		// passed to wp_signon(), which hashes it. Nonce verified above.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- see above; password must reach wp_signon() byte-for-byte.
		$user_password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';
		$remember      = ! empty( $_POST['remember'] );

		$credentials = array(
			'user_login'    => $user_login,
			'user_password' => $user_password,
			'remember'      => $remember,
		);

		$user = wp_signon( $credentials, is_ssl() );

		if ( is_wp_error( $user ) ) {
			$this->record_login_failure();
			wp_send_json_error(
				array(
					'message' => __( 'Invalid credentials. Please check your username/email and password.', 'smartpay' ),
				)
			);
		}

		do_action( 'smartpay_user_logged_in', $user );

		wp_send_json_success(
			array(
				'message'  => __( 'Login successful! Redirecting...', 'smartpay' ),
				'redirect' => $this->get_redirect_url( $user ),
			)
		);
	}

	public function enqueue_assets() {
		if ( ! $this->is_smartpay_login_page() ) {
			return;
		}
		wp_enqueue_style(
			'smartpay-login-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/css/frontend/login.css',
			array(),
			SMARTPAY_VERSION
		);
		wp_enqueue_script(
			'smartpay-login-frontend',
			SMARTPAY_PLUGIN_ASSETS . '/js/frontend/login.js',
			array(),
			SMARTPAY_VERSION,
			true
		);
		wp_localize_script(
			'smartpay-login-frontend',
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

	public function hide_page_title() {
		if ( $this->is_smartpay_login_page() ) {
			echo '<style>.wp-block-post-title,.entry-title,.page-title{display:none!important}</style>';
		}
	}

	public function inject_shortcode( $content ) {
		if ( $this->is_smartpay_login_page() && in_the_loop() && is_main_query() ) {
			return do_shortcode( '[smartpay_user_login]' );
		}
		return $content;
	}

	public function maybe_redirect() {
		if ( ! $this->is_smartpay_login_page() ) {
			return;
		}
		if ( is_user_logged_in() ) {
			$settings = get_option( 'smartpay_settings', array() );
			$page_id  = (int) ( $settings['customer_dashboard_page'] ?? 0 );
			wp_safe_redirect( $page_id ? get_permalink( $page_id ) : home_url() );
			exit;
		}
	}

	protected function is_smartpay_login_page() {
		$settings = get_option( 'smartpay_settings', array() );
		return ! empty( $settings['user_login_page'] ) && is_page( (int) $settings['user_login_page'] );
	}

	protected function is_login_rate_limited(): bool {
		$ip            = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$transient_key = 'smartpay_login_attempt_' . md5( $ip );
		$attempts      = get_transient( $transient_key );
		return $attempts && $attempts >= 5;
	}

	protected function record_login_failure(): void {
		$ip            = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$transient_key = 'smartpay_login_attempt_' . md5( $ip );
		$attempts      = (int) get_transient( $transient_key );
		set_transient( $transient_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
	}

	private function get_redirect_url( $user ) {
		if ( in_array( 'administrator', $user->roles ) ) {
			return admin_url();
		}

		$settings = get_option( 'smartpay_settings', array() );
		$page_id  = (int) ( $settings['customer_dashboard_page'] ?? 0 );
		if ( $page_id ) {
			return get_permalink( $page_id );
		}

		return home_url();
	}
}
