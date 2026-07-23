<?php
defined('ABSPATH') || exit;

class AddUuidColumnOnPaymentTable
{

    public static function up()
    {
        /**
         * global @var $wpdb
         * wp db object
         */
        global $wpdb;

        /**
         * get the prefix
         * smartpay product table name
         */
        $table = esc_sql( $wpdb->prefix . 'smartpay_payments' );
        $dbName = esc_sql( $wpdb->dbname );

        // Bail early if the table doesn't exist yet (e.g. during activation sandbox scrape).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
            return;
        }

        // check the uuid column exist on payments table
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$table' AND COLUMN_NAME = 'uuid'");

        // if found, then it is in the payments table
	    // if not, then add an uuid column after id
        if (empty($row)) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE $table ADD uuid VARCHAR(255) DEFAULT NULL AFTER id");
        }
    }
}
