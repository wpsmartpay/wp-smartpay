<?php

namespace SmartPay\Modules\Services;

defined( 'ABSPATH' ) || exit;

class SanitizerService {
	public function sanitize_personal_info( array $data ): array {
		return array(
			'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
			'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
			'email'      => sanitize_email( $data['email'] ?? '' ),
			'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
		);
	}

	public function sanitize_address( array $data ): array {
		return array(
			'address_line_1' => sanitize_text_field( $data['address_line_1'] ?? '' ),
			'address_line_2' => sanitize_text_field( $data['address_line_2'] ?? '' ),
			'city'           => sanitize_text_field( $data['city'] ?? '' ),
			'state'          => sanitize_text_field( $data['state'] ?? '' ),
			'postal_code'    => sanitize_text_field( $data['postal_code'] ?? '' ),
			'country'        => sanitize_text_field( $data['country'] ?? '' ),
		);
	}

	public function sanitize_registration_data( array $data ): array {
		return array_merge(
			$this->sanitize_personal_info( $data ),
			$this->sanitize_address( $data ),
			array(
				'password'             => $data['password'] ?? '',
				'confirm_password'     => $data['confirm_password'] ?? '',
				'subscribe_newsletter' => ! empty( $data['subscribe_newsletter'] ),
				'agree_terms'          => ! empty( $data['agree_terms'] ),
			)
		);
	}
}
