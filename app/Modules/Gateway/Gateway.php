<?php

namespace SmartPay\Modules\Gateway;

use SmartPay\Modules\Gateway\Gateways\PaypalStandard;

class Gateway
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->make(PaypalStandard::class);
    }

    public static function gateways()
    {
        return array(
            'paypal' => array(
                'admin_label'       => 'PayPal Standard',
                'checkout_label'    => 'PayPal'
            ),
        );
    }
}