<?php

namespace SmartPay\Framework\View;
defined('ABSPATH') || exit;

use SmartPay\Framework\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('view', function ($app) {
            return new View($app);
        }, 'View', View::class);
    }
}
