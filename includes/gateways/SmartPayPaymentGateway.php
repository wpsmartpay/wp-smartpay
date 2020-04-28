<?php

namespace ThemesGrove\SmartPay\Gateways;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
abstract class SmartPayPaymentGateway
{
    /**
     * Construct Paddle class.
     *
     * @since 0.1
     * @access public
     */
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