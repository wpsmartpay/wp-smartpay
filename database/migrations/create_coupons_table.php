<?php

use SmartPay\Models\Coupon;

class CreateSmartpayCouponsTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_coupons';

        $defaultStatus = Coupon::PUBLISH;

        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `discount_type` VARCHAR(50) NOT NULL,
                `discount_amount` FLOAT DEFAULT 0,
                `status` VARCHAR(45) DEFAULT '$defaultStatus',
                `expiry_date` TIMESTAMP NULL DEFAULT NULL,
                `created_by` BIGINT UNSIGNED DEFAULT 0,
                `extra` LONGTEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}