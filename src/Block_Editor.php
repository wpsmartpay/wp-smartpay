<?php

namespace SmartPay;

use SmartPay\Customers\SmartPay_Customer;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Block_Editor
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Block_Editor class.
     *
     * @since 0.0.1
     */
    private function __construct()
    {
        //
    }

    /**
     * Main Block_Editor Instance.
     *
     * Ensures that only one instance of Block_Editor exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     *
     * @return object|Block_Editor
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Block_Editor)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
