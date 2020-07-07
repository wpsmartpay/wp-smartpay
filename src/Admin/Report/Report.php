<?php

namespace SmartPay\Admin\Report;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Report
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Report class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 100);
    }

    /**
     * Main Report Instance.
     *
     * Ensures that only one instance of Report exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Report
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Report)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load admin scripts
     *
     * @return void
     */
    public function admin_scripts()
    {
        // Register scripts
        wp_register_script('smartpay-apexcharts', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/apexcharts.js', ['jquery'], SMARTPAY_VERSION);

        // Enqueue them
        wp_enqueue_script('smartpay-apexcharts');
    }
}
