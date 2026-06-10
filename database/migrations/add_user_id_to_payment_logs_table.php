<?php
defined( 'ABSPATH' ) || exit;

class Smartpay_AddUserIdToPaymentLogsTable {

	public static function up() {
		global $wpdb;

		$table = esc_sql( $wpdb->prefix . 'smartpay_payment_logs' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$column_exists = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", 'user_id' ) );

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `payment_id`" );
		}
	}
}
