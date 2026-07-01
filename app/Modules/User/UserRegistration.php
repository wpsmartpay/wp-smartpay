<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

use Exception;
use SmartPay\Modules\Services\CustomerService;
use SmartPay\Modules\Services\SanitizerService;
use SmartPay\Modules\Services\UserService;
use SmartPay\Modules\Services\ValidationService;
use WP_Error;

class UserRegistration {

	protected ValidationService $validator;
	protected SanitizerService $sanitizer;
	protected UserService $userService;
	protected CustomerService $customerService;

	public function __construct() {
		$this->validator       = new ValidationService();
		$this->sanitizer       = new SanitizerService();
		$this->userService     = new UserService();
		$this->customerService = new CustomerService();

		add_action( 'wp_ajax_nopriv_smartpay_user_registration', array( $this, 'handle_user_registration' ) );
		add_filter( 'template_include', array( $this, 'render_layout' ) );
	}

	public function handle_user_registration() {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}

		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( $this->is_registration_rate_limited() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Too many registration attempts. Please try again later.', 'smartpay' ),
				)
			);
		}

		$data              = $this->sanitizer->sanitize_registration_data( $_POST );
		$validation_errors = $this->validate_registration_data( $data );

		if ( $validation_errors->has_errors() ) {
			wp_send_json_error( array( 'errors' => $this->validator->format_errors( $validation_errors ) ) );
		}

		try {
			$user_id = $this->userService->create_user( $data );
			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( array( 'message' => __( 'User registration failed. Please try again later', 'smartpay' ) ) );
			}

			$customer = $this->customerService->create_customer( $user_id, $data );
			do_action( 'smartpay_after_user_registration', $user_id, $customer->id, $data );

			$this->userService->authenticate_user( $user_id );

			wp_send_json_success( array( 'message' => __( 'Registration successful!', 'smartpay' ) ) );

		} catch ( Exception $e ) {
			if ( isset( $user_id ) && ! is_wp_error( $user_id ) ) {
				wp_delete_user( $user_id );
			}

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Smartpay Customer creation failed: ' . $e->getMessage() );
			wp_send_json_error(
				array(
					'message' => __( 'Registration failed. Please try again later.', 'smartpay' ),
				)
			);
		}
	}

	protected function is_registration_rate_limited(): bool {
		$ip            = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$transient_key = 'smartpay_reg_attempt_' . md5( $ip );

		$attempts = get_transient( $transient_key );

		if ( $attempts && $attempts >= 3 ) {
			return true;
		}

		set_transient( $transient_key, $attempts ? $attempts + 1 : 1, MINUTE_IN_SECONDS );

		return false;
	}

	protected function validate_registration_data( $data ) {
		$errors = new WP_Error();

		$personal_info_errors = $this->validator->validate_personal_info( $data, true );
		$this->validator->merge_errors( $errors, $personal_info_errors );

		$address_errors = $this->validator->validate_address( $data );
		$this->validator->merge_errors( $errors, $address_errors );

		$password_errors = $this->validator->validate_password( $data['password'], $data['confirm_password'] );
		$this->validator->merge_errors( $errors, $password_errors );

		if ( empty( $data['agree_terms'] ) ) {
			$errors->add( 'agree_terms', __( 'You must agree to the terms and conditions', 'smartpay' ) );
		}

		return $errors;
	}

	public function render_layout( $template ) {
		if ( $this->is_smartpay_registration_page() ) {
			if ( is_user_logged_in() ) {
				$settings = get_option( 'smartpay_settings', array() );
				$page_id  = (int) ( $settings['customer_dashboard_page'] ?? 0 );
				if ( $page_id ) {
					wp_safe_redirect( get_permalink( $page_id ) );
				} else {
					wp_safe_redirect( home_url() );
				}
			}
			$shortcode = 'smartpay_user_registration';
			include SMARTPAY_DIR . 'resources/views/templates/layout.php';
			return;
		}

		return $template;
	}

	protected function is_smartpay_registration_page() {
		$settings = get_option( 'smartpay_settings', array() );
		return ! empty( $settings['user_registration_page'] ) && is_page( (int) $settings['user_registration_page'] );
	}
}
