<?php
defined( 'ABSPATH' ) || exit;

class Smartpay_CreateSmartpayPaymentLogsTable {

	public static function up() {
		global $wpdb;

		$table = esc_sql( $wpdb->prefix . 'smartpay_payment_logs' );

		$charsetCollate = $wpdb->get_charset_collate();

		// payment_logs table creation, caching not applicable.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			// Schema creation with dbDelta() on plugin activation.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
			$sql = "CREATE TABLE {$table} (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `payment_id` BIGINT UNSIGNED NOT NULL,
                `action` VARCHAR(100) NOT NULL DEFAULT 'note',
                `note` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) {$charsetCollate}";

			dbDelta( $sql );
		}
	}
}
