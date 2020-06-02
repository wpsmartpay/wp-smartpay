<?php

namespace SmartPay\Admin\Products;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Product
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Product class.
     *
     * @since 0.1
     */
    private function __construct()
    {
    }

    /**
     * Main Product Instance.
     *
     * Ensures that only one instance of Product exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|Product
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Product)) {
            self::$instance = new self();
            self::$instance->meta_box   = Meta_Box::instance();
        }

        return self::$instance;
    }
}