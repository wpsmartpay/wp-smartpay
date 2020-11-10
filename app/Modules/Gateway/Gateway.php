<?php

namespace SmartPay\Modules\Gateway;

class Gateway
{
    public function __construct()
    {
        //
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