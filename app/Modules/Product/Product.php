<?php

namespace SmartPay\Modules\Product;

class Product
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->registerAdminScripts();

        add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    protected function registerAdminScripts()
    {
        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);
    }

    public function adminScripts()
    {
        wp_enqueue_editor();
    }

    public function registerRestRoutes()
    {
        //
    }
}