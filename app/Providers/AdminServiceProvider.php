<?php

namespace SmartPay\Providers;

use SmartPay\Framework\Support\ServiceProvider;
use SmartPay\Modules\Admin\Admin;
use SmartPay\Modules\Admin\Report;
use SmartPay\Modules\Product\Product;
use SmartPay\Modules\Form\Form;
use SmartPay\Modules\Coupon\Coupon;
use SmartPay\Modules\Customer\Customer;
use SmartPay\Modules\Payment\Payment;
use SmartPay\Modules\Gateway\Gateway;

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

        $this->app->singleton(Product::class, function ($app) {
            return new Product($app);
        });

        $this->app->singleton(Form::class, function ($app) {
            return new Form($app);
        });

        $this->app->singleton(Coupon::class, function ($app) {
            return new Coupon($app);
        });

        $this->app->singleton(Customer::class, function ($app) {
            return new Customer($app);
        });

        $this->app->singleton(Payment::class, function ($app) {
            return new Payment($app);
        });

        $this->app->singleton(Gateway::class, function ($app) {
            return new Gateway($app);
        });
    }

    public function boot()
    {
        $this->app->make(Admin::class);
        $this->app->make(Report::class);
        $this->app->make(Product::class);
        $this->app->make(Form::class);
        $this->app->make(Coupon::class);
        $this->app->make(Customer::class);
        $this->app->make(Payment::class);
        $this->app->make(Gateway::class);
    }
}