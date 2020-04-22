<?php

namespace ThemesGrove\SmartPay;

use ThemesGrove\SmartPay\Admin\PaymentForm;
use ThemesGrove\SmartPay\Admin\Setting;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Admin
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Admin class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_menu', [$this, 'menu_item'], 10);


        add_action('admin_init', [$this, 'dbi_register_settings']);

        Setting::instance();

        PaymentForm::instance();
    }

    /**
     * Main Admin Instance.
     *
     * Ensures that only one instance of Admin exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Admin
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Admin)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function menu_item()
    {
        add_menu_page(
            __('WP SmartPay', 'wp-smartpay'),
            __('WP SmartPay', 'wp-smartpay'),
            'manage_options',
            'smartpay',
            [$this, 'smartpay_admin_dashboard_page_cb'],
            'dashicons-media-default',
            15
        );

        add_submenu_page(
            'smartpay',
            __('WP SmartPay - Dashboard', 'wp-smartpay'),
            __('Dashboard', 'wp-smartpay'),
            'manage_options',
            'smartpay',
        );

        add_submenu_page(
            'smartpay',
            __('WP SmartPay - Payment History', 'wp-smartpay'),
            __('Payment History', 'wp-smartpay'),
            'manage_options',
            'edit.php?post_type=smartpay_payment',
        );

        add_submenu_page(
            'smartpay',
            'WP SmartPay - Payment Forms',
            'Payment Forms',
            'manage_options',
            'edit.php?post_type=smartpay_form',
        );

        add_submenu_page(
            'smartpay',
            'WP SmartPay - Add Payment Forms',
            'Add Form',
            'manage_options',
            'post-new.php?post_type=smartpay_form',
        );

        add_submenu_page(
            'smartpay',
            'WP SmartPay - Setting',
            'Setting',
            'manage_options',
            'smartpay-setting',
            [$this, 'admin_setting_page_cb']
        );

        add_submenu_page(
            'smartpay',
            'WP SmartPay - Log',
            'Log',
            'manage_options',
            'smartpay-log',
            [$this, 'admin_log_page_cb']
        );
    }

    public function smartpay_admin_dashboard_page_cb()
    {
        return view('admin/dashboard');
    }

    public function admin_setting_page_cb()
    {
        return view('admin/setting');
    }

    public function admin_log_page_cb()
    {
        return view('admin/debug-log');
    }

    public function dbi_register_settings()
    {
        register_setting('dbi_example_plugin_options', 'dbi_example_plugin_options', [$this, 'dbi_example_plugin_options_validate']);
        add_settings_section('api_settings', 'API Settings', [$this, 'dbi_plugin_section_text'], 'dbi_example_plugin');

        add_settings_field('dbi_plugin_setting_api_key', 'API Key', [$this, 'dbi_plugin_setting_api_key'], 'dbi_example_plugin', 'api_settings');
        add_settings_field('dbi_plugin_setting_results_limit', 'Results Limit', [$this, 'dbi_plugin_setting_results_limit'], 'dbi_example_plugin', 'api_settings');
    }

    public function dbi_example_plugin_options_validate($input)
    {
        $newinput['api_key'] = trim($input['api_key']);
        if (!preg_match('/^[a-z0-9]{32}$/i', $newinput['api_key'])) {
            $newinput['api_key'] = '';
        }

        return $newinput;
    }

    public function dbi_plugin_section_text()
    {
        echo '<p>Here you can set all the options for using the API</p>';
    }

    public function dbi_plugin_setting_api_key()
    {
        $options = get_option('dbi_example_plugin_options');
        echo "<input id='dbi_plugin_setting_api_key' name='dbi_example_plugin_options[api_key]'type='text' value='" . esc_attr($options['api_key']) . "' />";
    }

    public function dbi_plugin_setting_results_limit()
    {
        $options = get_option('dbi_example_plugin_options');
        echo "<input id='dbi_plugin_setting_results_limit' name='dbi_example_plugin_options[results_limit]' type='text' value='" . esc_attr($options['results_limit']) . "' />";
    }
}