<?php

namespace SmartPay\Modules\Product;

use SmartPay\Framework\Application;
use SmartPay\Http\Controllers\Rest\Admin\ProductController;
use WP_REST_Server;

class Product
{
    protected $app;

    public function __construct(Application $app)
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
        $productController = $this->app->make(ProductController::class);

        register_rest_route('smartpay/v1', 'products', [
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

        register_rest_route('smartpay/v1', 'products/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$productController, 'show'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$productController, 'update'],
                'permission_callback' => [$productController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$productController, 'destroy'],
                'permission_callback' => [$productController, 'middleware'],
            ],
        ]);
    }
}
