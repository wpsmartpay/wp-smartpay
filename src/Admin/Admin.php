<?php

namespace SmartPay\Admin;

use SmartPay\Admin\Settings\Setting;
use SmartPay\Admin\Products\Product;
use SmartPay\Admin\Forms\Form;
use SmartPay\Admin\Payments\Payment;
use SmartPay\Admin\Report\Report;
use SmartPay\Admin\Utilities\Upload;
use SmartPay\Admin\Utilities\Install;
use SmartPay\Admin\Utilities\Uninstall;
use SmartPay\Admin\Integrations\Integrations;

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
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts'], 100);

        add_action('admin_menu', [$this, 'menu_item'], 10);
    }

    /**
     * Main Admin Instance.
     *
     * Ensures that only one instance of Admin exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Admin
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Admin)) {
            self::$instance = new self();

            self::$instance->admin_notices = Admin_Notices::instance();
            self::$instance->setting       = Setting::instance();
            self::$instance->integrations  = Integrations::instance();
            self::$instance->report        = Report::instance();
            self::$instance->product       = Product::instance();
            self::$instance->form          = Form::instance();
            self::$instance->payment       = Payment::instance();
            self::$instance->upload        = Upload::instance();
            self::$instance->block_editor  = Block_Editor::instance();
            self::$instance->install       = Install::instance();
            self::$instance->uninstall     = Uninstall::instance();
        }

        return self::$instance;
    }

    public function load_admin_scripts()
    {
        // Register scripts
        wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.min.css', '', SMARTPAY_VERSION);
        wp_register_script('smartpay-bootstrap', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/bootstrap.js', ['jquery'], SMARTPAY_VERSION);
        wp_register_script('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/js/admin.js', ['smartpay-bootstrap'], SMARTPAY_VERSION);
        wp_register_script('smartpay-icons', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/feather.min.js', ['smartpay-bootstrap'], SMARTPAY_VERSION, true);
        wp_register_script('smartpay-sweetalert', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/sweetalert.min.js', ['smartpay-bootstrap'], SMARTPAY_VERSION, true);

        // Enqueue them
        wp_enqueue_style('smartpay-admin');
        wp_localize_script(
            'smartpay-admin',
            'smartpay',
            array('ajax_url' => admin_url('admin-ajax.php'))
        );

        wp_enqueue_script('smartpay-bootstrap');
        wp_enqueue_script('smartpay-admin');
        wp_enqueue_script('smartpay-icons');
        wp_add_inline_script('smartpay-icons', 'feather.replace()');
        wp_enqueue_script('smartpay-sweetalert');
    }


    public function menu_item()
    {
        remove_submenu_page('edit.php?post_type=smartpay_product', 'post-new.php?post_type=smartpay_product');

        add_submenu_page(
            'edit.php?post_type=smartpay_product',
            'SmartPay - Payment Forms',
            'All Forms',
            'manage_options',
            'edit.php?post_type=smartpay_form'
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_product',
            __('SmartPay - Payment History', 'smartpay'),
            __('Payment History', 'smartpay'),
            'manage_options',
            'edit.php?post_type=smartpay_payment'
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_product',
            __('SmartPay - Customers', 'smartpay'),
            __('Customers', 'smartpay'),
            'manage_options',
            'smartpay-customers',
            function () {
                return smartpay_view('admin/customers/index');
            }
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_product',
            'SmartPay - Settings',
            'Settings',
            'manage_options',
            'smartpay-setting',
            function () {
                return smartpay_view('admin/setting');
            }
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_product',
            'SmartPay - Reports',
            'Reports',
            'manage_options',
            'smartpay-reports',
            function () {
                return smartpay_view('admin/reports');
            }
        );

        add_submenu_page(
            'edit.php?post_type=smartpay_product',
            'SmartPay - integrations',
            'Integrations',
            'manage_options',
            'smartpay-integrations',
            function () {
                return smartpay_view('admin/integrations');
            }
        );

        // add_submenu_page(
        //     'edit.php?post_type=smartpay_product',
        //     'SmartPay - Log',
        //     'Log',
        //     'manage_options',
        //     'smartpay-log',
        //     function () {
        //         return smartpay_view('admin/debug-log');
        //     }
        // );
    }
}