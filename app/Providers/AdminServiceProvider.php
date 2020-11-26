<?php

namespace SmartPay\Providers;

use SmartPay\Framework\Support\ServiceProvider;
use SmartPay\Modules\Admin\Admin;
use SmartPay\Modules\Admin\Report;
use SmartPay\Modules\Admin\Utilities\Upload;

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

        $this->app->singleton(Upload::class, function ($app) {
            return new Upload();
        });
    }

    public function boot()
    {
        $this->app->make(Admin::class);
        $this->app->make(Report::class);
        $this->app->make(Upload::class);
    }
}