<?php

namespace SmartPay\Modules\Customer;

use SmartPay\Http\Controllers\Rest\Admin\CustomerController;
use WP_REST_Server;

class Customer
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function adminScripts()
    {
        //
    }

    public function registerRestRoutes()
    {
        $productController = $this->app->make(CustomerController::class);

        register_rest_route('smartpay/v1', 'customers', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'index'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            // [
            //     'methods'   => WP_REST_Server::CREATABLE,
            //     'callback'  => [$productController, 'store'],
            //     'permission_callback' => [$productController, 'middleware'],
            // ],
        ]);

        register_rest_route('smartpay/v1', 'customers/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'show'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            // [
            //     'methods'   => 'PUT, PATCH',
            //     'callback'  => [$productController, 'update'],
            //     'permission_callback' => [$productController, 'middleware'],
            // ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$productController, 'destroy'],
                'permission_callback' => [$productController, 'middleware'],
            ],
        ]);
    }
}