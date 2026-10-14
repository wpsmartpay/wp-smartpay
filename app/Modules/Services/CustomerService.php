<?php

namespace SmartPay\Modules\Services;

defined( 'ABSPATH' ) || exit;

use SmartPay\Models\Customer;

class CustomerService {
	public function create_customer( int $user_id, array $data ): Customer {
		$customer = new Customer();
		$this->populate_customer( $customer, $data );
		$customer->user_id = $user_id;
		$customer->save();

		return $customer;
	}

	public function update_personal_info( Customer $customer, array $data ) {
		$customer->first_name = $data['first_name'];
		$customer->last_name  = $data['last_name'];
		$customer->email      = $data['email'];

		if ( ! empty( $data['phone'] ) ) {
			$customer->phone = $data['phone'];
		}

		$customer->save();

		return $customer;
	}

	public function update_address( Customer $customer, array $data ): Customer {
		$customer->address_line_1 = $data['address_line_1'];
		$customer->address_line_2 = $data['address_line_2'];
		$customer->city           = $data['city'];
		$customer->state          = $data['state'];
		$customer->postal_code    = $data['postal_code'];
		$customer->country        = strtoupper( $data['country'] );

		$customer->save();

		return $customer;
	}

	public function update_preferences( Customer $customer, array $data ): Customer {
		$customer->subscribe_newsletter = ! empty( $data['subscribe_newsletter'] );
		$customer->save();

		return $customer;
	}

	public function populate_customer( Customer $customer, array $data ) {
		if ( isset( $data['first_name'] ) ) {
			$customer->first_name = $data['first_name'];
		}
		if ( isset( $data['last_name'] ) ) {
			$customer->last_name = $data['last_name'];
		}
		if ( isset( $data['email'] ) ) {
			$customer->email = $data['email'];
		}
		if ( isset( $data['phone'] ) ) {
			$customer->phone = $data['phone'];
		}

		// Address
		if ( isset( $data['address_line_1'] ) ) {
			$customer->address_line_1 = $data['address_line_1'];
		}
		if ( isset( $data['address_line_2'] ) ) {
			$customer->address_line_2 = $data['address_line_2'];
		}
		if ( isset( $data['city'] ) ) {
			$customer->city = $data['city'];
		}
		if ( isset( $data['state'] ) ) {
			$customer->state = $data['state'];
		}
		if ( isset( $data['postal_code'] ) ) {
			$customer->postal_code = $data['postal_code'];
		}
		if ( isset( $data['country'] ) ) {
			$customer->country = $data['country'];
		}

		// Preference
		if ( isset( $data['subscribe_newsletter'] ) ) {
			$customer->subscribe_newsletter = (bool) $data['subscribe_newsletter'];
		}
	}
}
