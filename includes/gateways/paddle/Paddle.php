<?php

namespace ThemesGrove\SmartPay\Gateways;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Paddle extends PaymentGateway
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Paddle class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Main Paddle Instance.
     *
     * Ensures that only one instance of Paddle exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Paddle
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Paddle)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}