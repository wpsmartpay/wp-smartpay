<?php

namespace ThemesGrove\SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Setting
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Setting class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Main Setting Instance.
     *
     * Ensures that only one instance of Setting exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Setting
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Setting)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function get_settings()
    {
        $settings = get_option('smartpay_settings');

        if (empty($settings)) {
            $general_settings = get_option('smartpay_settings_general') ? get_option('smartpay_settings_general') : [];
            $gateway_settings = get_option('smartpay_settings_gateways') ? get_option('smartpay_settings_gateways') : [];
            $email_settings   = get_option('smartpay_settings_emails') ? get_option('smartpay_settings_emails') : [];
            $license_settings = get_option('smartpay_settings_licenses') ? get_option('smartpay_settings_licenses') : [];

            $settings = array_merge($general_settings, $gateway_settings, $email_settings, $license_settings);
            update_option('smartpay_settings', $settings);
        }
        return apply_filters('smartpay_get_settings', $settings);
    }
}
