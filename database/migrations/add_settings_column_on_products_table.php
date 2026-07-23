<?php
defined('ABSPATH') || exit;

class AddSettingsColumnOnProductTable
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
        $table = esc_sql( $wpdb->prefix . 'smartpay_products' );
        $dbName = esc_sql( $wpdb->dbname );

        // Bail early if the table doesn't exist yet (e.g. during activation sandbox scrape).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
            return;
        }

        // check the settings column exist on products table and safe prepare
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$table' AND COLUMN_NAME = 'settings'");

        // if no row found then create a new column on the products table
        // after the status table
        if (empty($row)) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE $table ADD settings LONGTEXT DEFAULT NULL AFTER status");
        }
    }
}
