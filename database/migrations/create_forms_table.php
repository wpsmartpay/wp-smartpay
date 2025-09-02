<?php

use SmartPay\Models\Form;

class CreateSmartpayFormsTable
{
    public static function up()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'smartpay_forms';

        $defaultStatus = Form::PUBLISH;

        $charsetCollate = $wpdb->get_charset_collate();

		// forms Table Creation, caching not applicable.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
			// Schema creation with dbDelta() on plugin activation.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
            $sql = "CREATE TABLE $table (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `amounts` LONGTEXT DEFAULT NULL,
                `body` LONGTEXT DEFAULT NULL,
                `settings` LONGTEXT DEFAULT NULL,
                `fields` LONGTEXT DEFAULT NULL,
                `status` VARCHAR(45) NOT NULL DEFAULT '$defaultStatus',
                `extra` LONGTEXT DEFAULT NULL,
                `created_by` BIGINT UNSIGNED DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) $charsetCollate";

            dbDelta($sql);
        }
    }
}
