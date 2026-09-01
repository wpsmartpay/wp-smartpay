<?php

namespace SmartPay;
defined('ABSPATH') || exit;

class Updater
{
    public function __construct()
    {
        // if needed to update/add new table or column
        $this->_update_database_if_available();
    }

    /**
     * boot the class from static call
     * @return Updater
     */
    public static function boot(): Updater
    {
        return new self();
    }

    /**
     * do staff, when add/update previous database
     * @return void
     */
    public function _update_database_if_available()
    {
        // Only run schema migrations in contexts where it is safe to do so.
        // Frontend page loads and AJAX payment callbacks must never pay the
        // cost of multiple INFORMATION_SCHEMA queries on every request.
        if ( ! is_admin() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }

        // Version sentinel: skip all migrations when the stored DB version
        // already matches the running plugin version. Migrations are only
        // re-run after a plugin update bumps SMARTPAY_VERSION.
        $stored_version = get_option( 'smartpay_db_version', '' );
        if ( SMARTPAY_VERSION === $stored_version ) {
            return;
        }

        // dbDelta() lives in upgrade.php which is not loaded on the frontend.
        // Require it here — inside the guard — so it is only pulled in when
        // we are actually going to run migrations.
        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        \Smartpay_AddSettingsColumnOnProductTable::up();
        \Smartpay_AddUuidColumnOnPaymentTable::up();
        \Smartpay_CreateSmartpayPaymentLogsTable::up();
        \Smartpay_AddUserIdToPaymentLogsTable::up();

        // Record that migrations have run for this plugin version so
        // subsequent requests skip the INFORMATION_SCHEMA queries above.
        // autoload = false keeps this out of wp_options alloptions cache.
        update_option( 'smartpay_db_version', SMARTPAY_VERSION, false );
    }
}
