<?php

namespace SmartPay\Modules\Admin;

use SmartPay\Http\Controllers\Admin\ProductController;
use SmartPay\Http\Controllers\Admin\FormController;
use SmartPay\Http\Controllers\Admin\CustomerController;
use SmartPay\Models\Product;

class Admin
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->registerAdminScripts();
        $this->registerAdminMenu();
    }

    protected function registerAdminScripts()
    {
        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);
    }

    protected function registerAdminMenu()
    {
        $this->app->addAction('admin_menu', [$this, 'adminMenu']);
    }

    public function adminMenu()
    {
        add_menu_page(
            __('Dashboard', 'smartpay'),
            __('SmartPay', 'smartpay'),
            'manage_options',
            'smartpay',
            function () {
                echo view('admin/admin', ['abc' => 123]);
            },
            smartpay_svg_icon(),
            25
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Products', 'smartpay'),
            __('Products', 'smartpay'),
            'manage_options',
            'smartpay-products',
            [$this, 'renderProductPage']
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Forms', 'smartpay'),
            __('Forms', 'smartpay'),
            'manage_options',
            'smartpay-forms',
            [$this, 'formRoute']
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Customers', 'smartpay'),
            __('Customers', 'smartpay'),
            'manage_options',
            'smartpay-customers',
            [$this, 'customerRoute']
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Payments', 'smartpay'),
            __('Payments', 'smartpay'),
            'manage_options',
            'smartpay-payments',
            [$this, 'customerRoute']
        );
    }

    public function adminScripts()
    {
        wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.css', '', SMARTPAY_VERSION);
        wp_enqueue_style('smartpay-admin');

        wp_register_script('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/js/admin.js', ['jquery'], SMARTPAY_VERSION);
        wp_enqueue_script('smartpay-admin');
        wp_localize_script(
            'smartpay-admin',
            'smartpay',
            array('ajax_url' => admin_url('admin-ajax.php'))
        );
    }

    public function renderProductPage()
    {
        $page = $_GET['page'] ?? '';
        $action = $_GET['action'] ?? 'index';

        if ('smartpay-products' !== $page) {
            return;
        }

        $controller =  $this->app->make(ProductController::class);

        if ('index' === $action) {
            $controller->index();
        }

        if ('create' === $action) {
            $controller->create();
        }

        if ('edit' === $action) {
            $productId = $_GET['id'] ?? 0;
            if (!!$productId) {
                $controller->edit(Product::findOrFail($productId));
            }
        }
    }

    public function formRoute()
    {
        $action = $_GET['action'] ?? 'index';

        if (method_exists(FormController::class, $action)) {
            echo $this->app->make(FormController::class)->$action();
        } else {
            echo 'Route not found!';
        }
    }

    public function customerRoute()
    {
        $action = $_GET['action'] ?? 'index';

        if (method_exists(CustomerController::class, $action)) {
            echo $this->app->make(CustomerController::class)->$action();
        } else {
            echo 'Route not found!';
        }
    }
}