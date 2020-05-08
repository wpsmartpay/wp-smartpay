<?php

namespace SmartPay\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
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
        Admin_Notices::instance();

        Setting::instance();

        Download::instance();

        Payment_Form::instance();

		add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts'], 100);

        add_action('admin_menu', [$this, 'menu_item'], 10);
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

    public function load_admin_scripts()
    {
        // Register scripts
		wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.min.css', '', SMARTPAY_VERSION);
        wp_register_script('smartpay-bootstrap', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/bootstrap.js', ['jquery'], SMARTPAY_VERSION);
        wp_register_script('smartpay-icons', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/feather.min.js', ['smartpay-bootstrap'], SMARTPAY_VERSION, true);
        wp_register_script('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/js/admin.js', ['smartpay-bootstrap'], SMARTPAY_VERSION);

		// Enqueue them
		wp_enqueue_style('smartpay-admin');
        wp_enqueue_script('smartpay-bootstrap');
        wp_enqueue_script('smartpay-icons');
		wp_enqueue_script('smartpay-admin');
		wp_add_inline_script( 'smartpay-icons', 'feather.replace()' );
	}


    public function menu_item()
    {
        remove_submenu_page( 'edit.php?post_type=smartpay_download', 'post-new.php?post_type=smartpay_download' );

        add_submenu_page(
            'edit.php?post_type=smartpay_download',
            'SmartPay - Payment Forms',
            'All Forms',
            'manage_options',
            'edit.php?post_type=smartpay_form',
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_download',
            __('SmartPay - Add New', 'smartpay'),
            __('Add new', 'smartpay'),
            'manage_options',
            '#',
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_download',
            __('SmartPay - Payment History', 'smartpay'),
            __('Payment History', 'smartpay'),
            'manage_options',
            'edit.php?post_type=smartpay_payment',
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_download',
            __('SmartPay - Customers', 'smartpay'),
            __('Customers', 'smartpay'),
            'manage_options',
            '#',
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_download',
            'SmartPay - Settings',
            'Settings',
            'manage_options',
            'smartpay-setting',
            [$this, 'admin_setting_page_cb']
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_download',
            'SmartPay - Log',
            'Log',
            'manage_options',
            'smartpay-log',
            [$this, 'admin_log_page_cb']
        );
    }

    public function smartpay_admin_dashboard_page_cb()
    {
        return smartpay_view('admin/dashboard');
    }

    public function admin_setting_page_cb()
    {
        return smartpay_view('admin/setting');
    }

    public function admin_log_page_cb()
    {
        return smartpay_view('admin/debug-log');
    }
}