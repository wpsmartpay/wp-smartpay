<?php

namespace SmartPay\Providers;

use SmartPay\Modules\Admin\Admin;
use SmartPay\Modules\Product\Product;
use SmartPay\Modules\Form\Form;
use SmartPay\Modules\Customer\Customer;
use SmartPay\Modules\Payment\Payment;
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

        $this->app->singleton(Form::class, function ($app) {
            return new Form($app);
        });

        $this->app->singleton(Customer::class, function ($app) {
            return new Customer($app);
        });

        $this->app->singleton(Payment::class, function ($app) {
            return new Payment($app);
        });
    }

    public function boot()
    {
        $this->app->make(Admin::class);
        $this->app->make(Product::class);
        $this->app->make(Form::class);
        $this->app->make(Customer::class);
        $this->app->make(Payment::class);
    }
}