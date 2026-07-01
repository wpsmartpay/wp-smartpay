<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

use Exception;
use WP_Error;
use SmartPay\Modules\Services\CustomerService;
use SmartPay\Modules\Services\SanitizerService;
use SmartPay\Modules\Services\UserService;
use SmartPay\Modules\Services\ValidationService;

class UserProfile {

	protected ValidationService $validator;
	protected SanitizerService $sanitizer;
	protected UserService $userService;
	protected CustomerService $customerService;
	public function __construct() {
		$this->validator       = new ValidationService();
		$this->sanitizer       = new SanitizerService();
		$this->userService     = new UserService();
		$this->customerService = new CustomerService();

		add_filter( 'template_include', array( $this, 'render_layout' ) );

		add_action( 'wp_ajax_smartpay_upload_avatar', array( $this, 'handle_avatar_upload' ) );
		add_filter( 'get_avatar_url', array( $this, 'custom_avatar_url' ), 10, 3 );
		add_action( 'wp_ajax_smartpay_remove_avatar', array( $this, 'handle_avatar_remove' ) );

		add_action( 'wp_ajax_smartpay_update_personal_info', array( $this, 'handle_personal_info_update' ) );
		add_action( 'wp_ajax_smartpay_update_address', array( $this, 'handle_address_update' ) );
		add_action( 'wp_ajax_smartpay_update_password', array( $this, 'handle_password_update' ) );
		add_action( 'wp_ajax_smartpay_update_preferences', array( $this, 'handle_preferences_update' ) );
	}

	public function render_layout( $template ) {
		if ( $this->is_smartpay_profile_page() ) {
			if ( ! is_user_logged_in() ) {
				$settings = get_option( 'smartpay_settings', array() );
				$page_id  = (int) ( $settings['user_login_page'] ?? 0 );
				if ( $page_id ) {
					wp_safe_redirect( get_permalink( $page_id ) );
				} else {
					wp_safe_redirect( home_url() );
				}
			} elseif ( ! smartpay_is_customer() ) {
				wp_safe_redirect( home_url() );
			}
			$shortcode = 'smartpay_user_profile';
			include SMARTPAY_DIR . 'resources/views/templates/layout.php';
			return;
		}

		return $template;
	}

	protected function is_smartpay_profile_page() {
		$settings = get_option( 'smartpay_settings', array() );
		return ! empty( $settings['user_profile_page'] ) && is_page( (int) $settings['user_profile_page'] );
	}

	public function handle_avatar_upload() {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}
		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'smartpay' ) ) );
		}

		$current_user = wp_get_current_user();

		if ( empty( $_FILES['avatar'] ) || ! isset( $_FILES['avatar']['error'] ) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error(
				array(
					'message' => __( 'No file uploaded or upload error occurred.', 'smartpay' ),
				)
			);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- individual keys are sanitized below (name via sanitize_file_name, type via wp_check_filetype)
		$file         = $_FILES['avatar'];
		$file['name'] = sanitize_file_name( $file['name'] );

		$allowed_files = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );
		$file_type     = wp_check_filetype( $file['name'] )['type'];

		if ( ! in_array( $file_type, $allowed_files ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid file type. Only JPG, PNG, and GIF are allowed.', 'smartpay' ),
				)
			);
		}

		if ( $file['size'] > 2 * 1024 * 1024 ) {
			wp_send_json_error(
				array(
					'message' => __( 'File size must be less than 2MB', 'smartpay' ),
				)
			);
		}

		// WP Native file upload handler
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$old_avatar_id = get_user_meta( $current_user->ID, 'smartpay_avatar_id', true );
		if ( $old_avatar_id ) {
			wp_delete_attachment( $old_avatar_id, true );
		}

		$upload_overrides = array(
			'test_form' => false,
			'mimes'     => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'gif'          => 'image/gif',
			),
		);

		$uploaded_file = wp_handle_upload( $file, $upload_overrides );
		if ( isset( $uploaded_file['error'] ) ) {
			wp_send_json_error(
				array(
					'message' => $uploaded_file['error'],
				)
			);
		}

		$attachment_data = array(
			'post_mime_type' => $uploaded_file['type'],
			'post_title'     => sanitize_file_name( pathinfo( $uploaded_file['file'], PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment_data, $uploaded_file['file'] );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to create attachment.', 'smartpay' ),
				)
			);
		}

		$attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

		update_user_meta( $current_user->ID, 'smartpay_avatar_id', $attachment_id );

		$avatar_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
		do_action( 'smartpay_after_avatar_upload', $current_user->ID, $attachment_id );

		wp_send_json_success(
			array(
				'message'    => __( 'Profile picture updated successfully', 'smartpay' ),
				'avatar_url' => $avatar_url,
			)
		);
	}

	public function custom_avatar_url( $url, $id_or_email, $args ) {
		$user = false;

		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', $id_or_email );
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ) {
			$user = get_user_by( 'id', $id_or_email->user_id );
		} elseif ( is_string( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
		}

		if ( ! $user ) {
			return $url;
		}

		$avatar_id = get_user_meta( $user->ID, 'smartpay_avatar_id', true );

		if ( $avatar_id ) {
			$custom_url = wp_get_attachment_image_url( $avatar_id, 'thumbnail' );
			return $custom_url ?: $url;
		}
		return $url;
	}

	public function handle_avatar_remove() {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}
		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'smartpay' ) ) );
		}

		$current_user = wp_get_current_user();

		$avatar_id = get_user_meta( $current_user->ID, 'smartpay_avatar_id', true );

		if ( $avatar_id ) {
			wp_delete_attachment( $avatar_id, true );
			delete_user_meta( $current_user->ID, 'smartpay_avatar_id' );
		}

		$default_avatar = get_avatar_url( $current_user->ID );
		do_action( 'smartpay_after_avatar_remove', $current_user->ID );

		wp_send_json_success(
			array(
				'message'    => __( 'Profile picture removed successfully!', 'smartpay' ),
				'avatar_url' => $default_avatar,
			)
		);
	}

	public function handle_personal_info_update() {
		if ( ! $this->verify_ajax_request() ) {
			return;
		}

		$current_user = wp_get_current_user();
		$customer     = smartpay_get_customer_by_user_id( $current_user->ID );

		if ( ! $customer ) {
			wp_send_json_error( array( 'message' => __( 'Customer Not Found', 'smartpay' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified via verify_ajax_request() → check_ajax_referer()
		$data              = $this->sanitizer->sanitize_personal_info( $_POST );
		$validation_errors = $this->validator->validate_personal_info( $data, true, $current_user->ID );

		if ( $validation_errors->has_errors() ) {
			wp_send_json_error( array( 'errors' => $this->validator->format_errors( $validation_errors ) ) );
		}

		try {
			$user_id = $this->userService->update_user( $current_user->ID, $data );
			if ( is_wp_error( $user_id ) ) {
				throw new Exception( $user_id->get_error_message() );
			}

			$this->customerService->update_personal_info( $customer, $data );

			do_action( 'smartpay_after_personal_info_update', $current_user->ID, $customer->id );

			wp_send_json_success( array( 'message' => 'Personal Information updated successfully!' ) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update personal information. Please try again.', 'smartpay' ) ) );
		}
	}

	public function handle_address_update() {
		if ( ! $this->verify_ajax_request() ) {
			return;
		}

		$current_user = wp_get_current_user();
		$customer     = smartpay_get_customer_by_user_id( $current_user->ID );

		if ( ! $customer ) {
			wp_send_json_error( array( 'message' => __( 'Customer not found.', 'smartpay' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified via verify_ajax_request() → check_ajax_referer()
		$data              = $this->sanitizer->sanitize_address( $_POST );
		$validation_errors = $this->validator->validate_address( $data );

		if ( $validation_errors->has_errors() ) {
			wp_send_json_error( array( 'errors' => $this->validator->format_errors( $validation_errors ) ) );
		}

		try {
			$this->customerService->update_address( $customer, $data );

			do_action( 'smartpay_after_address_update', $current_user->ID, $customer->id );

			wp_send_json_success( array( 'message' => __( 'Address updated successfully!', 'smartpay' ) ) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update address. Please try again.', 'smartpay' ) ) );
		}
	}

	public function handle_password_update() {
		if ( ! $this->verify_ajax_request() ) {
			return;
		}

		$current_user = wp_get_current_user();
		$customer     = smartpay_get_customer_by_user_id( $current_user->ID );

		if ( ! $customer ) {
			wp_send_json_error( array( 'message' => __( 'Customer not found.', 'smartpay' ) ) );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified via verify_ajax_request() → check_ajax_referer()
		$current_password     = sanitize_text_field( wp_unslash( $_POST['current_password'] ?? '' ) );
		$new_password         = sanitize_text_field( wp_unslash( $_POST['new_password'] ?? '' ) );
		$confirm_new_password = sanitize_text_field( wp_unslash( $_POST['confirm_new_password'] ?? '' ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$errors = new WP_Error();

		if ( empty( $current_password ) ) {
			$errors->add( 'current_password', __( 'Current password is required.', 'smartpay' ) );
		} elseif ( ! wp_check_password( $current_password, $current_user->user_pass, $current_user->ID ) ) {
			$errors->add( 'current_password', __( 'Current password is incorrect.', 'smartpay' ) );
		}

		$password_errors = $this->validator->validate_password( $new_password, $confirm_new_password );
		$this->validator->merge_errors( $errors, $password_errors );

		if ( $errors->has_errors() ) {
			wp_send_json_error( array( 'errors' => $this->validator->format_errors( $errors ) ) );
		}

		try {
			$this->userService->update_password( $current_user->ID, $new_password, true );

			do_action( 'smartpay_after_password_update', $current_user->ID );

			wp_send_json_success( array( 'message' => __( 'Password updated successfully! Other sessions have been logged out.', 'smartpay' ) ) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update password. Please try again.', 'smartpay' ) ) );
		}
	}

	public function handle_preferences_update() {
		if ( ! $this->verify_ajax_request() ) {
			return;
		}

		$current_user = wp_get_current_user();
		$customer     = smartpay_get_customer_by_user_id( $current_user->ID );

		if ( ! $customer ) {
			wp_send_json_error( array( 'message' => __( 'Customer not found.', 'smartpay' ) ) );
		}

		try {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified via verify_ajax_request() → check_ajax_referer()
			$this->customerService->update_preferences( $customer, $_POST );

			do_action( 'smartpay_after_preferences_update', $current_user->ID, $customer->id );

			wp_send_json_success( array( 'message' => __( 'Preferences updated successfully!', 'smartpay' ) ) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update preferences. Please try again.', 'smartpay' ) ) );
		}
	}

	protected function verify_ajax_request(): bool {
		if ( ! wp_doing_ajax() ) {
			wp_die();
		}

		check_ajax_referer( 'smartpay_frontend_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'smartpay' ) ) );
			return false;
		}
		return true;
	}
}
