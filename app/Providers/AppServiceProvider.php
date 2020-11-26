<?php

namespace SmartPay\Providers;

use SmartPay\Framework\Support\ServiceProvider;
use SmartPay\Modules\Product\Product;
use SmartPay\Modules\Form\Form;
use SmartPay\Modules\Coupon\Coupon;
use SmartPay\Modules\Customer\Customer;
use SmartPay\Modules\Payment\Payment;
use SmartPay\Modules\Gateway\Gateway;
use SmartPay\Modules\Frontend\Common;
use SmartPay\Modules\Shortcode\Shortcode;
use SmartPay\Modules\Integration\Integration;
use SmartPay\Modules\Email\Email;
use SmartPay\Modules\Frontend\Utilities\Downloader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Email::class, function ($app) {
            return new Email($app);
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

        $this->app->singleton(Common::class, function ($app) {
            return new Common($app);
        });

        $this->app->singleton(Shortcode::class, function ($app) {
            return new Shortcode($app);
        });

        $this->app->singleton(Integration::class, function ($app) {
            return new Integration($app);
        });

        $this->app->singleton(Downloader::class, function ($app) {
            return new Downloader($app);
        });
    }

    public function boot()
    {
        $this->app->make(Email::class);
        $this->app->make(Product::class);
        $this->app->make(Form::class);
        $this->app->make(Coupon::class);
        $this->app->make(Customer::class);
        $this->app->make(Payment::class);
        $this->app->make(Gateway::class);
        $this->app->make(Common::class);
        $this->app->make(Shortcode::class);
        $this->app->make(Integration::class);
    }
}
