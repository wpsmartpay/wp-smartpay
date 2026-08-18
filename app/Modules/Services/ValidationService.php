<?php

namespace SmartPay\Modules\Services;

defined( 'ABSPATH' ) || exit;

use WP_Error;

class ValidationService {

	public function validate_personal_info( array $data, bool $check_email_exists = false, int $exclude_user_id = 0 ): WP_Error {
		$errors = new WP_Error();

		$first_name = $data['first_name'] ?? '';
		$last_name  = $data['last_name'] ?? '';
		$email      = $data['email'] ?? '';
		$phone      = $data['phone'] ?? '';

		if ( empty( $first_name ) ) {
			$errors->add( 'first_name', __( 'First name is required.', 'smartpay' ) );
		} elseif ( strlen( $first_name ) > 100 ) {
			$errors->add( 'first_name', __( 'First name is too long.', 'smartpay' ) );
		}

		if ( empty( $last_name ) ) {
			$errors->add( 'last_name', __( 'Last name is required.', 'smartpay' ) );
		} elseif ( strlen( $last_name ) > 100 ) {
			$errors->add( 'last_name', __( 'Last name is too long.', 'smartpay' ) );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors->add( 'email', __( 'Valid Email is required.', 'smartpay' ) );
		} else {
			if ( $check_email_exists ) {
				$existing_user = email_exists( $email );
				if ( $existing_user && $existing_user != $exclude_user_id ) {
					$errors->add( 'email', __( 'Email already exists.', 'smartpay' ) );
				}
			}
			if ( $this->is_email_disposable( $email ) ) {
				$errors->add( 'email', __( 'Disposable email address are not allowed.', 'smartpay' ) );
			}
		}

		if ( ! empty( $phone ) && ! preg_match( '/^[\d\s\-\+\(\)]+$/', $phone ) ) {
			$errors->add( 'phone', __( 'Invalid phone number format.', 'smartpay' ) );
		}

		return $errors;
	}

	public function validate_address( array $data ): WP_Error {
		$errors = new WP_Error();

		$address_line_1 = $data['address_line_1'] ?? '';
		$address_line_2 = $data['address_line_2'] ?? '';
		$city           = $data['city'] ?? '';
		$state          = $data['state'] ?? '';
		$postal_code    = $data['postal_code'] ?? '';
		$country        = $data['country'] ?? '';

		if ( empty( $address_line_1 ) ) {
			$errors->add( 'address_line_1', __( 'Street address is required.', 'smartpay' ) );
		} elseif ( strlen( $address_line_1 ) > 255 ) {
			$errors->add( 'address_line_1', __( 'Street address is too long.', 'smartpay' ) );
		}

		if ( strlen( $address_line_2 ) > 255 ) {
			$errors->add( 'address_line_2', __( 'Address line 2 is too long.', 'smartpay' ) );
		}

		if ( empty( $city ) ) {
			$errors->add( 'city', __( 'City is required.', 'smartpay' ) );
		} elseif ( strlen( $city ) > 100 ) {
			$errors->add( 'city', __( 'City name is too long.', 'smartpay' ) );
		}

		if ( empty( $state ) ) {
			$errors->add( 'state', __( 'State is required.', 'smartpay' ) );
		} elseif ( strlen( $state ) > 100 ) {
			$errors->add( 'state', __( 'State name is too long.', 'smartpay' ) );
		}

		if ( empty( $postal_code ) ) {
			$errors->add( 'postal_code', __( 'Postal code is required.', 'smartpay' ) );
		} elseif ( strlen( $postal_code ) > 20 ) {
			$errors->add( 'postal_code', __( 'Postal code is too long.', 'smartpay' ) );
		}

		if ( empty( $country ) ) {
			$errors->add( 'country', __( 'Country is required.', 'smartpay' ) );
		} elseif ( strlen( $country ) !== 2 || ! $this->is_valid_country_code( $country ) ) {
			$errors->add( 'country', __( 'Invalid country code.', 'smartpay' ) );
		}

		return $errors;
	}

	public function validate_password( string $password, ?string $confirm_password = null ): WP_Error {
		$errors = new WP_Error();

		if ( empty( $password ) ) {
			$errors->add( 'password', __( 'Password is required.', 'smartpay' ) );
		} elseif ( strlen( $password ) < 8 ) {
			$errors->add( 'password', __( 'Password must be at least 8 characters.', 'smartpay' ) );
		} elseif ( strlen( $password ) > 128 ) {
			$errors->add( 'password', __( 'Password is too long.', 'smartpay' ) );
		} elseif ( ! $this->is_password_strong_enough( $password ) ) {
			$errors->add( 'password', __( 'Password must contain at least one letter and one number.', 'smartpay' ) );
		}

		if ( $confirm_password !== null && $password !== $confirm_password ) {
			$errors->add( 'password', __( 'Passwords do not match.', 'smartpay' ) );
		}

		return $errors;
	}

	public function merge_errors( WP_Error $target, WP_Error $source ): void {
		foreach ( $source->get_error_codes() as $code ) {
			$target->add( $code, $source->get_error_message( $code ) );
		}
	}

	public function format_errors( WP_Error $errors ): array {
		$error_data = array();
		foreach ( $errors->get_error_codes() as $code ) {
			$error_data[ $code ] = $errors->get_error_message( $code );
		}
		return $error_data;
	}

	protected function is_email_disposable( string $email ) {
		$disposable_domains = apply_filters(
			'smartpay_disposable_email_domains',
			array(
				'tempmail.com',
				'10minutemail.com',
				'mailinator.com',
				'guerrillamail.com',
				'maildrop.cc',
			)
		);

		$domain = substr( strchr( $email, '@' ), 1 );

		return in_array( strtolower( $domain ), $disposable_domains, true );
	}

	protected function is_password_strong_enough( $password ) {
		return preg_match( '/[A-Za-z]/', $password ) && preg_match( '/[0-9]/', $password );
	}

	protected function is_valid_country_code( $country ) {
		return preg_match( '/^[A-Z]{2}$/', strtoupper( $country ) );
	}
}
