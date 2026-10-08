<?php
defined( 'ABSPATH' ) || exit;

/**
 * Flags existing forms that hold a donation block (anonymous, comment, tribute)
 * as donation forms, so their donor input is kept. New and re-saved forms are
 * flagged by Campaign::flag_donation_form(); this covers forms saved before it.
 */
class Smartpay_FlagFormsWithDonationBlocks {

	const OPTION = 'smartpay_donation_block_forms_flagged';

	public static function up() {
		global $wpdb;

		if ( get_option( self::OPTION ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s",
				'smartpay_form',
				'%' . $wpdb->esc_like( '<!-- wp:smartpay-form/donation-' ) . '%'
			)
		);

		foreach ( $form_ids as $form_id ) {
			update_post_meta( (int) $form_id, '_smartpay_is_donation_form', true );
		}

		update_option( self::OPTION, 1, false );
	}
}
