<?php

namespace SmartPay\Framework\Validation;

use SmartPay\Framework\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('validator', function ($app) {
            return new Validator($app);
        });
    }
}