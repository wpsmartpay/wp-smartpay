<?php

namespace SmartPay\Modules\Gateway;

use SmartPay\Modules\Gateway\Gateways\PaypalStandard;
use SmartPay\Modules\Gateway\Gateways\ManualPurchase\FreePurchase;

class Gateway
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->make(PaypalStandard::class);
        $this->app->make(FreePurchase::class);
    }

    public static function gateways()
    {
        return array(
            'paypal' => array(
                'admin_label'       => 'PayPal Standard',
                'checkout_label'    => 'PayPal',
                'gateway_icon'    => SMARTPAY_PLUGIN_ASSETS .'/img/paypal.png',
            ),

            'free' => array(
                'admin_label'       => 'Free Purchase',
                'checkout_label'    => 'Free'
            ),
        );
    }
}