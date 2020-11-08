<?php

namespace SmartPay\Providers;

use SmartPay\Modules\Admin\Admin;
use SmartPay\Modules\Admin\Form;
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

        $this->app->singleton(Form::class, function ($app) {
            return new Form($app);
        });
    }

    public function boot()
    {
        $this->app->make(Admin::class);
        $this->app->make(Form::class);
    }
}
