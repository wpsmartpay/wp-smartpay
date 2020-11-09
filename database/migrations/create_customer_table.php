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
                `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `user_id` BIGINT(20) NOT NULL,
                `first_name` MEDIUMTEXT NOT NULL,
                `last_name` MEDIUMTEXT NOT NULL,
                `email` VARCHAR(45) NOT NULL,
                `payments` LONGTEXT  NOT NULL,
                `notes` LONGTEXT NOT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                UNIQUE KEY email (email)
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}