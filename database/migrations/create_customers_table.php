<?php

class CreateSmartpayCustomersTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_customers';

        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED DEFAULT NULL,
                `first_name` MEDIUMTEXT NOT NULL,
                `last_name` MEDIUMTEXT DEFAULT NULL,
                `email` VARCHAR(75) NOT NULL,
                `notes` TEXT DEFAULT NULL,
                `extra` LONGTEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY email (email)
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}
