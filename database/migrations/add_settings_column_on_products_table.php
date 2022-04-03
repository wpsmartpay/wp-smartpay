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

        // check the settings column exist on products table
        $row = $wpdb->get_results("SELECT settings FROM INFORMATION_SCHEMA.COLUMNS WHERE $table = $table AND column_name = 'settings'");

        // if no row found then create a new column on the products table
        // after the status table
        if (empty($row)) {
            $wpdb->query("ALTER TABLE $table ADD settings LONGTEXT DEFAULT NULL AFTER status");
        }
    }
}
