<?php

namespace SmartPay\Utilities;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Install
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Install class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Main Install Instance.
     *
     * Ensures that only one instance of Install exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Install
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Install)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}