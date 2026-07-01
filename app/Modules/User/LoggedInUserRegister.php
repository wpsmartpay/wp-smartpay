<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

use Exception;
use SmartPay\Modules\Services\CustomerService;
use SmartPay\Modules\Services\SanitizerService;
use SmartPay\Modules\Services\ValidationService;
use WP_Error;

class LoggedInUserRegister {
	protected SanitizerService $sanitizer;
	protected ValidationService $validator;
	protected CustomerService $customerService;
	public function __construct() {
		$this->sanitizer       = new SanitizerService();
		$this->validator       = new ValidationService();
		$this->customerService = new CustomerService();

		add_action( 'wp_ajax_smartpay_complete_profile', array( $this, 'handle_complete_profile' ) );
	}

	public function handle_complete_profile() {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}

		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Must be logged in' ) );
		}

		$current_user     = wp_get_current_user();
		$current_password = sanitize_text_field( wp_unslash( $_POST['current_password'] ?? '' ) );

		$errors = new WP_Error();

		$data                         = $this->sanitizer->sanitize_address( $_POST );
		$data['subscribe_newsletter'] = ! empty( $_POST['subscribe_newsletter'] );

		$validation_errors = $this->validator->validate_address( $data );
		$this->validator->merge_errors( $errors, $validation_errors );

		if ( empty( $current_password ) ) {
			$errors->add( 'current_password', __( 'Current password is required.', 'smartpay' ) );
		} elseif ( ! wp_check_password( $current_password, $current_user->user_pass, $current_user->ID ) ) {
			$errors->add( 'current_password', __( 'Current password is incorrect.', 'smartpay' ) );
		}

		if ( $errors->has_errors() ) {
			wp_send_json_error( array( 'errors' => $this->validator->format_errors( $errors ) ) );
		}

		try {
			$data['first_name'] = $current_user->user_firstname;
			$data['last_name']  = $current_user->user_lastname;
			$data['email']      = $current_user->user_email;

			$this->customerService->create_customer( $current_user->ID, $data );
			$current_user->add_cap( 'access_smartpay_dashboard' );

			if ( current_user_can( 'edit_posts' ) ) {
				$current_user->remove_cap( 'deny_wp_admin' );
			} elseif ( ! in_array( 'smartpay_customer', $current_user->roles, true ) ) {
					$current_user->add_role( 'smartpay_customer' );
			}

			do_action( 'smartpay_after_logged_in_user_register' );

			wp_send_json_success( array( 'message' => __( 'Profile Completed!', 'smartpay' ) ) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => __( 'Failed to complete profile', 'smartpay' ) ) );
		}
	}
}
