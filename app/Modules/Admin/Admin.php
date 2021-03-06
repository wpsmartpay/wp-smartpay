<?php

namespace SmartPay\Modules\Admin;

use SmartPay\Modules\Admin\Report;
use SmartPay\Modules\Admin\Setting;

class Admin
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->build(Setting::class);

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);
        $this->app->addAction('admin_menu', [$this, 'adminMenu']);
        $this->app->addAction('wp_ajax_smartpay_debug_log_clear', [$this, 'smartpayDebugLogClear']);
    }

    public function adminMenu()
    {
        add_menu_page(
            __('Dashboard', 'smartpay'),
            __('SmartPay', 'smartpay'),
            'manage_options',
            'smartpay',
            function () {
                echo smartpay_view('admin');
            },
            smartpay_svg_icon(),
            25
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Products', 'smartpay'),
            __('Products', 'smartpay'),
            'manage_options',
            'smartpay#/products',
            function () {
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Forms', 'smartpay'),
            __('Forms', 'smartpay'),
            'manage_options',
            'smartpay-form',
            function () {
                echo smartpay_view('form-builder');
            }
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Customers', 'smartpay'),
            __('Customers', 'smartpay'),
            'manage_options',
            'smartpay#/customers',
            function () {
                echo smartpay_view('admin');
            }
        );

        // add_submenu_page(
        //     'smartpay',
        //     __('SmartPay - Coupons', 'smartpay'),
        //     __('Coupons', 'smartpay'),
        //     'manage_options',
        //     'smartpay#/coupons',
        //     function () {
        //         echo smartpay_view('admin');
        //     }
        // );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Payments', 'smartpay'),
            __('Payments', 'smartpay'),
            'manage_options',
            'smartpay#/payments',
            function () {
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Settings', 'smartpay'),
            __('Settings', 'smartpay'),
            'manage_options',
            'smartpay-setting',
            function () {
                echo smartpay_view('settings');
            }
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Integrations', 'smartpay'),
            __('Integrations', 'smartpay'),
            'manage_options',
            'smartpay-integrations',
            function () {
                echo smartpay_view('integrations');
            }
        );


        $this->smartpayProMenu();

        do_action('smartpay_admin_add_menu_items');
    }

    private function smartpayProMenu()
    {
        if (!array_key_exists('smartpay-pro/smartpay-pro.php', get_plugins())) {
            global $submenu;

            $submenu['smartpay'][99] = [
                "⭐ Upgrade to pro",
                "manage_options",
                'https://wpsmartpay.com',
            ];
        }
    }

    public function adminScripts($hook)
    {

        wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.css', '', SMARTPAY_VERSION);
        wp_enqueue_style('smartpay-admin');
        if ('toplevel_page_smartpay' === $hook) {

            wp_register_script('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/js/admin.js', ['jquery', 'wp-element', 'wp-data'], SMARTPAY_VERSION, true);
            wp_enqueue_script('smartpay-admin');
            wp_localize_script(
                'smartpay-admin',
                'smartpay',
                array(
                    'restUrl'  => get_rest_url('', 'smartpay'),
                    'adminUrl'  => admin_url('admin.php'),
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'apiNonce' => wp_create_nonce('wp_rest'),
                    'options' => $this->getOptionsScriptsData(),
                )
            );

            // WARN: Enqueue to bottom
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        if ('smartpay_page_smartpay-setting' === $hook) {
            wp_enqueue_script('smartpay-debug-log', SMARTPAY_PLUGIN_ASSETS . '/js/debuglog.js', ['jquery'], SMARTPAY_VERSION, true);
            wp_localize_script(
                'smartpay-debug-log',
                'debugLog',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );
        }

        $this->registerBlocks($hook);
    }

    public function registerBlocks($hook)
    {
        // Exclude blocks from the form-builder
        if ('smartpay_page_smartpay-form' === $hook) {
            return;
        }

        // Global
        wp_enqueue_script('smartpay-editor-blocks', SMARTPAY_PLUGIN_ASSETS . '/blocks/index.js', ['wp-element', 'wp-plugins', 'wp-blocks', 'wp-block-editor', 'wp-data']);

        // Product
        register_block_type('smartpay/product', array(
            'editor_script' => 'smartpay-editor-blocks',
        ));

        // Form
        register_block_type('smartpay/form', array(
            'editor_script' => 'smartpay-editor-blocks',
        ));

        wp_localize_script(
            'smartpay-editor-blocks',
            'smartpay',
            array(
                'restUrl'  => get_rest_url('', 'smartpay'),
                'adminUrl'  => admin_url('admin.php'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'apiNonce' => wp_create_nonce('wp_rest'),
            )
        );
    }


    /**
     * Get options data for localize scripts
     *
     * @return array
     */
    protected function getOptionsScriptsData(): array
    {
        return [
            'currency'          => smartpay_get_currency(),
            'currencySymbol'    => smartpay_get_currency_symbol(),
            'isTestMode'        => smartpay_is_test_mode(),
        ];
    }

    public function smartpayDebugLogClear()
    {
        $smartpayLogs = new Logger();
        $smartpayLogs->clear_log_file();

        $smartpay_settings = get_option('smartpay_settings', []);
        $smartpay_settings['smartpay_debug_log'] = null;
        update_option('smartpay_settings', $smartpay_settings);
        die();
    }
}