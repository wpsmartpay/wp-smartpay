<?php

namespace SmartPay\Gateways;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Gateway
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Gateway class.
     *
     * @since  0.0.1
     * @access private
     */
    private function __construct()
    {
        // Initialize actions.
        $this->include_gateways();
    }

    /**
     * Main Gateway Instance.
     *
     * Ensures that only one instance of Gateway exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  0.0.1
     * @return object|Gateway
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Gateway)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function gateways()
    {
        return array(
            'paypal' => array(
                'admin_label'       => 'PayPal Standard',
                'checkout_label'    => 'PayPal'
            ),
        );
    }

    public function include_gateways()
    {
        // TODO: Update method to include
        Paypal_Standard::instance();
    }
}
