<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

class UserLogin {

	public function __construct() {
		add_action( 'wp_ajax_nopriv_smartpay_user_login', array( $this, 'handle_user_login' ) );
		add_filter( 'template_include', array( $this, 'render_layout' ) );
	}

	public function handle_user_login() {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}

		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => 'You are already logged in.',
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

		$user_login    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$user_password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
		$remember      = ! empty( $_POST['remember'] );

		$credentials = array(
			'user_login'    => $user_login,
			'user_password' => $user_password,
			'remember'      => $remember,
		);

		$user = wp_signon( $credentials, is_ssl() );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error(
				array(
					'message' => 'Invalid credentials. Please check your username/email and password.',
				)
			);
		}

		wp_send_json_success(
			array(
				'message'  => 'Login successful! Redirecting...',
				'redirect' => $this->get_redirect_url( $user ),
			)
		);
	}

	public function render_layout( $template ) {
		if ( $this->is_smartpay_login_page() ) {
			if ( is_user_logged_in() ) {
				$settings = get_option( 'smartpay_settings', array() );
				$page_id  = (int) ( $settings['customer_dashboard_page'] ?? 0 );
				if ( $page_id ) {
					wp_safe_redirect( get_permalink( $page_id ) );
				} else {
					wp_safe_redirect( home_url() );
				}
			}
			$shortcode = 'smartpay_user_login';
			include SMARTPAY_DIR . 'resources/views/templates/layout.php';
			return;
		}

		return $template;
	}

	protected function is_smartpay_login_page() {
		$settings = get_option( 'smartpay_settings', array() );
		return ! empty( $settings['user_login_page'] ) && is_page( (int) $settings['user_login_page'] );
	}

	protected function is_login_rate_limited(): bool {
		$ip            = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$transient_key = 'smartpay_login_attempt_' . md5( $ip );

		$attempts = get_transient( $transient_key );

		if ( $attempts && $attempts >= 3 ) {
			return true;
		}

		set_transient( $transient_key, $attempts ? $attempts + 1 : 1, MINUTE_IN_SECONDS );

		return false;
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
