<?php

class CreateSmartpayCouponsTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_coupons';

        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `discount_type` VARCHAR(50) NOT NULL,
                `discount_amount` FLOAT DEFAULT 0,
                `status` VARCHAR(45) NULL DEFAULT 'Draft',
                `expiry_date` TIMESTAMP NULL,
                `created_by` INT NULL,
                `extra` TEXT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}