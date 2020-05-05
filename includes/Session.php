<?php

namespace SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Session
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Session class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Main Session Instance.
     *
     * Ensures that only one instance of Session exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Session
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Session)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
