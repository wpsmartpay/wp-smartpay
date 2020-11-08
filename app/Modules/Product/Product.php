<?php

namespace SmartPay\Modules\Product;

use SmartPay\Http\Controllers\Rest\ProductController as ProductRestController;
use WP_REST_Server;

class Product
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
        $productController = $this->app->make(ProductRestController::class);

        register_rest_route('smartpay/v1/', 'products', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'index'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::CREATABLE,
                'callback'  => [$productController, 'store'],
                'permission_callback' => [$productController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1/', 'products/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'view'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$productController, 'update'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$productController, 'delete'],
                'permission_callback' => [$productController, 'middleware'],
            ],
        ]);
    }
}