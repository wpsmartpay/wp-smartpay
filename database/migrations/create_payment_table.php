<?php

class CreateSmartpayPaymentsTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_payments';

        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `type` TEXT NULL,
                `data` LONGTEXT NULL,
                `amount` FLOAT NULL,
                `currency` VARCHAR(3) NULL,
                `gateway` VARCHAR(75) NULL,
                `transaction_id` VARCHAR(255) NULL,
                `customer` INT UNSIGNED NULL,
                `email` VARCHAR(75) NOT NULL,
                `key` VARCHAR(255) NULL,
                `parent_payment` INT UNSIGNED NULL,
                `mode` VARCHAR(75) NULL,
                `status` VARCHAR(75) NULL,
                `extra` LONGTEXT NULL,
                `completed_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}