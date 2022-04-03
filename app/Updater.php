<?php

namespace SmartPay;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Updater
{
    private const SMARTPAY_CURRENT_DB_VERSION = '1.1';
    private const SMARTPAY_NEW_DB_VERSION = '1.2';
    public function __construct()
    {
        $this->_set_smartpay_db_version();
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

    public function _set_smartpay_db_version()
    {
        // after adding the migration on database, should set the db version into wp options table
        // that will help us to compare with previous version
        $smartpay_db_version =get_option( 'smartpay_db_version') ?? null;
        if (!$smartpay_db_version){
            add_option('smartpay_db_version', self::SMARTPAY_CURRENT_DB_VERSION);
        }
    }

    /**
     * do staff, when add/update previous database
     * @return void
     */
    public function _update_database_if_available()
    {
        // update tables when plugin updating
        //first add the version to wp_options table
        $oldVersion = get_option( 'smartpay_db_version') ?? null;
        // FIXME: should check with version compare
        if (floatval($oldVersion) < floatval(self::SMARTPAY_NEW_DB_VERSION)) {
            \AddSettingsColumnOnProductTable::up();
            update_option('smartpay_db_version', self::SMARTPAY_NEW_DB_VERSION);
        }
    }
}