<?php

namespace SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

abstract class Payment_Gateway
{
    public function __construct()
    {
        //
    }

    public function register_gateway($gateways)
    {
        //
    }

    public function process_payment($payment_data)
    {
        //
    }

    public function process_recurring()
    {
        //
    }

    public function process_webhooks()
    {
        if (isset($_GET['smartpay-listener']) && $_GET['smartpay-listener'] == 'test') {
            echo 'test webhook';
            die();
        }
    }
}