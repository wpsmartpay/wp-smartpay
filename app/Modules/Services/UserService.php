<?php

namespace SmartPay\Modules\Services;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_Session_Tokens;

class UserService {
	public function create_user( array $data ): int|WP_Error {
		$user_id = wp_insert_user(
			array(
				'first_name'    => $data['first_name'],
				'last_name'     => $data['last_name'],
				'user_email'    => $data['email'],
				'user_login'    => sanitize_user( $data['email'], true ),
				'role'          => 'smartpay_customer',
				'user_pass'     => $data['password'],
				'user_nicename' => sanitize_title( "{$data['first_name']}-{$data['last_name']}" ),
				'display_name'  => "{$data['first_name']} {$data['last_name']}",
			)
		);

		if ( ! is_wp_error( $user_id ) ) {
			$user = get_user_by( 'id', $user_id );
			$user->add_cap( 'access_smartpay_dashboard' );
		}
		return $user_id;
	}

	public function update_user( int $user_id, array $data ): int|WP_Error {
		return wp_update_user(
			array(
				'ID'            => $user_id,
				'first_name'    => $data['first_name'],
				'last_name'     => $data['last_name'],
				'user_email'    => $data['email'],
				'user_login'    => $data['email'],
				'user_nicename' => sanitize_title( "{$data['first_name']}-{$data['last_name']}" ),
				'display_name'  => "{$data['first_name']} {$data['last_name']}",
			)
		);
	}

	public function update_password( int $user_id, string $new_password, bool $destroy_other_sessions = true ) {
		wp_set_password( $new_password, $user_id );

		if ( $destroy_other_sessions ) {
			$sessions = WP_Session_Tokens::get_instance( $user_id );
			$sessions->destroy_others( wp_get_session_token() );
		}
	}

	public function authenticate_user( int $user_id, bool $remember = true ) {
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, $remember );
	}
}
