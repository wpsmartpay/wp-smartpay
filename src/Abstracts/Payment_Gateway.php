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
        $listener  = sanitize_text_field($_GET['smartpay-listener']) ?? '';

        if (isset($listener) && $listener == 'test') {
            echo 'test webhook';
            die();
        }
    }
}