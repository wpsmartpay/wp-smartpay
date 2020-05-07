<?php

namespace SmartPay\Admin;

use SmartPay\Admin\Downloads\Meta_Box;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Download
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Download class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        Meta_Box::instance();
    }

    /**
     * Main Download Instance.
     *
     * Ensures that only one instance of Download exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|Download
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Download)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
