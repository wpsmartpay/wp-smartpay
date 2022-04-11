<?php

namespace SmartPay;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

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
        \AddSettingsColumnOnProductTable::up();
    }
}