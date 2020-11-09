<?php

namespace SmartPay;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Activator
{
    public function __construct()
    {
        $this->migrate();
    }

    public static function boot()
    {
        return new self();
    }

    public function migrate()
    {
        \CreateSmartpayProductsTable::up();
        \CreateSmartpayCouponsTable::up();
        \CreateSmartpayCustomersTable::up();
    }
}