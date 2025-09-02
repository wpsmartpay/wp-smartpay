<?php

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
        $table = $wpdb->prefix . 'smartpay_products';
        $dbName = $wpdb->dbname;

        // check the settings column exist on products table
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_results("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '$dbName' AND
            TABLE_NAME = '$table' AND
            COLUMN_NAME = 'settings'
        ");

        // if no row found then create a new column on the products table
        // after the status table
        if (empty($row)) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $wpdb->query("ALTER TABLE $table ADD settings LONGTEXT DEFAULT NULL AFTER status");
        }
    }
}
