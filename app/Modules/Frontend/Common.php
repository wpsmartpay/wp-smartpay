<?php

namespace SmartPay\Modules\Frontend;

use SmartPay\Modules\Frontend\Utilities\Downloader;
use SmartPay\Http\Controllers\Rest\CustomerController;
use WP_REST_Server;

class Common
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->make(Downloader::class);

        $this->app->addAction('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function enqueueScripts()
    {
        wp_register_style('smartpay-app', SMARTPAY_PLUGIN_ASSETS . '/css/app.css', '', SMARTPAY_VERSION);
        wp_enqueue_style('smartpay-app');

        wp_register_script('smartpay-bootstrap', SMARTPAY_PLUGIN_ASSETS . '/js/bootstrap.js', ['jquery'], SMARTPAY_VERSION);
        wp_enqueue_script('smartpay-bootstrap');
        wp_register_script('smartpay-app', SMARTPAY_PLUGIN_ASSETS . '/js/app.js', ['jquery'], SMARTPAY_VERSION, true);
        wp_enqueue_script('smartpay-app');
        wp_localize_script(
            'smartpay-app',
            'smartpay',
            array(
                'restUrl'  => get_rest_url('', 'smartpay'),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'apiNonce' => wp_create_nonce('wp_rest'),

                'options' => $this->getOptionsScriptsData(),
            )
        );
    }

    public function registerRestRoutes()
    {
        $customerController = $this->app->make(CustomerController::class);

        register_rest_route('smartpay/v1/public', 'customers/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$customerController, 'show'],
                'permission_callback' => [$customerController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$customerController, 'update'],
                'permission_callback' => [$customerController, 'middleware'],
            ],
        ]);
    }

    /**
     * Get options data for localize scripts
     *
     * @return array
     */
    protected function getOptionsScriptsData(): array
    {
        // global $smartpay_options;

        return [
            'currency'          => smartpay_get_currency(),
            'currencySymbol'    => smartpay_get_currency_symbol(),
            'isTestMode'        => smartpay_is_test_mode(),
        ];
    }
}
