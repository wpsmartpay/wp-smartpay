<?php

namespace SmartPay\Providers;

use SmartPay\Modules\Admin\Admin;
use SmartPay\Framework\Support\ServiceProvider;
use SmartPay\Models\Product;

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
    }

    public function boot()
    {
        $this->app->make(Admin::class);
    }
}
