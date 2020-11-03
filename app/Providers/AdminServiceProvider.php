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


        // echo '<pre>';
        // var_dump(Product::find(3));
        // die();

        // \CreateProductsTable::up();



        $this->app->addAction('admin_init', function () {
            if ('smartpay-products' == ($_GET['page'] ?? '') && 'store' == ($_GET['action'] ?? '')) {
                echo 'product add';

                die();
            }
        });
    }
}