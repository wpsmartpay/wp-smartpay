<?php

use SmartPay\Models\Product;

class CreateSmartpayProductsTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_products';

        $defaultStatus = Product::PUBLISH;

        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) DEFAULT NULL,
                `description` TEXT NULL,
                `base_price` FLOAT DEFAULT 0,
                `sale_price` FLOAT DEFAULT 0,
                `files` LONGTEXT DEFAULT NULL,
                `covers` LONGTEXT DEFAULT NULL,
                `parent` INT UNSIGNED DEFAULT 0,
                `status` VARCHAR(45) NOT NULL DEFAULT '$defaultStatus',
                `created_by` INT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}
