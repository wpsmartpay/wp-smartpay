<?php

namespace SmartPay\Providers;

use SmartPay\Modules\Admin\Admin;
use SmartPay\Modules\Product\Product;
use SmartPay\Framework\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Admin::class, function ($app) {
            return new Admin($app);
        });

        $this->app->singleton(Product::class, function ($app) {
            return new Product($app);
        });
    }

    public function boot()
    {
        $this->app->make(Admin::class);
        $this->app->make(Product::class);
    }
}