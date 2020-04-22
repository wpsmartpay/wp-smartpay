<?php

namespace ThemesGrove\SmartPay\Gateways;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
abstract class PaymentGateway
{
    /**
     * Construct Paddle class.
     *
     * @since 0.1
     * @access public
     */
    public function __construct()
    {
    }

    public function register_gateway($gateways)
    {
        // $gateways['St'] = 'st';
        return $gateways;
    }
}