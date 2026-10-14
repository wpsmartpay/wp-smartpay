<?php
defined( 'ABSPATH' ) || exit;

/**
 * Adds `is_donation` to payments so donations and sales live in separate lists.
 *
 * The flag is stamped once when a payment is created (see Payment::boot()).
 * Existing rows are backfilled here, only on the run that adds the column, so
 * moving a form between campaigns later never reclassifies old payments.
 */
class Smartpay_AddIsDonationColumnOnPaymentsTable {

	/**
	 * Set once the column exists. Payment::boot() only stamps the flag after
	 * this, so a checkout between a plugin update and the first admin load
	 * (when migrations run) never writes to a missing column.
	 */
	const OPTION = 'smartpay_payments_is_donation_column';

	public static function up() {
		global $wpdb;

		$table = esc_sql( $wpdb->prefix . 'smartpay_payments' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$column_exists = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", 'is_donation' ) );

		if ( ! empty( $column_exists ) ) {
			update_option( self::OPTION, 1 );
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$added = $wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `is_donation` TINYINT(1) NOT NULL DEFAULT 0 AFTER `type`, ADD INDEX `is_donation` (`is_donation`)" );

		// ALTER denied or timed out: leave the option unset so checkout never
		// writes to the missing column, and tell the Updater to retry.
		if ( false === $added ) {
			return false;
		}

		self::backfill( $table );

		update_option( self::OPTION, 1 );
	}

	/**
	 * Flag existing payments on donation forms, then their renewals.
	 *
	 * @param string $table Escaped payments table name.
	 */
	private static function backfill( string $table ) {
		global $wpdb;

		if ( ! function_exists( 'smartpay_payments_form_match_sql' ) ) {
			return;
		}

		// Raw SQL, not smartpay_get_donation_form_ids(): migrations run before
		// `init`, when the campaign taxonomy is not registered and a tax_query
		// would match nothing.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- core table names.
		$form_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				 JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				 WHERE tt.taxonomy = %s
				 UNION
				 SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = '1'",
				'smartpay_campaign',
				'_smartpay_is_donation_form'
			)
		);
		// phpcs:enable

		if ( empty( $form_ids ) ) {
			return;
		}

		$match = smartpay_payments_form_match_sql( array_map( 'intval', $form_ids ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $match is prepared.
		$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET is_donation = 1 WHERE type = %s AND {$match}", 'form_payment' ) );

		// Renewals carry no form id; they follow their parent.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "UPDATE `{$table}` child JOIN `{$table}` parent ON child.parent_id = parent.id SET child.is_donation = 1 WHERE parent.is_donation = 1" );
	}
}
