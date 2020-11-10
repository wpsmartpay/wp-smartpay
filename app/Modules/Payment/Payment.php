<?php

namespace SmartPay\Modules\Payment;

use SmartPay\Http\Controllers\Rest\Admin\PaymentController;
use WP_REST_Server;

class Payment
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
        $paymentController = $this->app->make(PaymentController::class);

        register_rest_route('smartpay/v1', 'payments', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$paymentController, 'index'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::CREATABLE,
                'callback'  => [$paymentController, 'store'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1', 'payments/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$paymentController, 'view'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$paymentController, 'update'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$paymentController, 'delete'],
                'permission_callback' => [$paymentController, 'middleware'],
            ],
        ]);
    }
}