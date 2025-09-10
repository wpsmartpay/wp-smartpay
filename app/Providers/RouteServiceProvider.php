<?php

namespace SmartPay\Providers;

use SmartPay\Framework\Http\Request;
use SmartPay\Framework\Support\ServiceProvider;

use SmartPay\Http\Controllers\Admin\ProductController;
use SmartPay\Models\Product;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAdminRoutes();
    }

    public function boot()
    {
        //
    }

    public function registerAdminRoutes()
    {
        $this->app->addAction('admin_init', [$this, 'productRoute']);
    }

    // TODO: Rewrite and Moved to route, It's just for test
    public function productRoute()
    {
	    // phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
        $page = $_GET['page'] ?? '';
	    // phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
        $action = $_GET['action'] ?? 'index';


        if ('smartpay-products' !== $page || !in_array($action, ['store', 'update'])) {
            return;
        }

        $controller =  $this->app->make(ProductController::class);

        if ('store' === $action) {
            $request = Request::createFromGlobals();
            $controller->store($request);
        }

        if ('update' === $action) {
	        // phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
            $productId = $_GET['id'] ?? 0;

            if (!!$productId) {
                $request = Request::createFromGlobals();
                $controller->update(Product::findOrFail($productId), $request);
            }
        }
    }
}
