<?php

namespace SmartPay\Support;

defined('ABSPATH') || exit;

abstract class ServiceProvider
{
    /**
     * Create a new service provider instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}