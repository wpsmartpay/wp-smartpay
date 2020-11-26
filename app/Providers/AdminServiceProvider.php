<?php

namespace SmartPay\Providers;

use SmartPay\Framework\Support\ServiceProvider;
use SmartPay\Modules\Admin\Admin;
use SmartPay\Modules\Admin\Report;
use SmartPay\Modules\Admin\Utilities\WPHooks;

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

        $this->app->singleton(Report::class, function ($app) {
            return new Report($app);
        });

        $this->app->singleton(WPHooks::class, function ($app) {
            return new WPHooks();
        });
    }

    public function boot()
    {
        $this->app->make(Admin::class);
        $this->app->make(Report::class);
        $this->app->make(WPHooks::class);
    }
}
