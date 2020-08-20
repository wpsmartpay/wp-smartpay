<?php

namespace SmartPay\Admin\Utilities;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Uninstall
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Uninstall class.
     *
     * @since 0.0.2
     * @access private
     */
    private function __construct()
    {
        register_deactivation_hook(SMARTPAY_FILE, [$this, 'deactivate']);
    }

    /**
     * Main Uninstall Instance.
     *
     * Ensures that only one instance of Uninstall exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.2
     * @return object|Uninstall
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Uninstall)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Plugin deactivate.
     *
     * @since 0.0.2
     * @access public
     * @return void
     */
    public function deactivate()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
