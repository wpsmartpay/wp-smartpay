<?php

namespace SmartPay\Customers;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Customer
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Customer class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Main Customer Instance.
     *
     * Ensures that only one instance of Customer exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Customer
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Customer)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function create_db_table()
    {
        return (new DB_Customer)->create_table();
    }
}