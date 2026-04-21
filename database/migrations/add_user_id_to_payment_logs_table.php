<?php

defined( 'ABSPATH' ) || exit;

class AddUserIdToPaymentLogsTable {

	public static function up() {
		global $wpdb;

		$table = $wpdb->prefix . 'smartpay_payment_logs';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", 'user_id' ) );

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `user_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `payment_id`" );
		}
	}
}
