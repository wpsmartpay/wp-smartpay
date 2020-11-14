<?php

namespace SmartPay\Modules\Admin;

use SmartPay\Http\Controllers\Rest\Admin\ReportController;

class Report
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function registerRestRoutes()
    {
        $reportController = $this->app->make(ReportController::class);

        register_rest_route('smartpay/v1', 'reports', [
            [
                'methods'   => \WP_REST_Server::READABLE,
                'callback'  => [$reportController, 'index'],
                'permission_callback' => [$reportController, 'middleware'],
            ],
        ]);
    }
}