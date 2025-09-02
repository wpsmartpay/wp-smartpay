<?php

use SmartPay\Models\Payment;

class CreateSmartpayPaymentsTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_payments';

        $defaultType = Payment::PRODUCT_PURCHASE;
        $defaultStatus = Payment::PENDING;

        $charsetCollate = $wpdb->get_charset_collate();

		// payments Table Creation, caching not applicable.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
			// Schema creation with dbDelta() on plugin activation.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
            $sql = "CREATE TABLE $table (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `type` VARCHAR(25) DEFAULT '$defaultType',
                `data` LONGTEXT DEFAULT NULL,
                `amount` FLOAT DEFAULT 0,
                `currency` VARCHAR(3) DEFAULT NULL,
                `gateway` VARCHAR(75) DEFAULT NULL,
                `transaction_id` VARCHAR(255) DEFAULT NULL,
                `customer_id` BIGINT UNSIGNED DEFAULT 0,
                `email` VARCHAR(75) NOT NULL,
                `key` VARCHAR(255) DEFAULT NULL,
                `parent_id` BIGINT UNSIGNED DEFAULT 0,
                `mode` VARCHAR(75) DEFAULT 'Live',
                `status` VARCHAR(75) DEFAULT '$defaultStatus',
                `extra` LONGTEXT DEFAULT NULL,
                `completed_at` DATETIME DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}
