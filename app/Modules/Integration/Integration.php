<?php

namespace SmartPay\Modules\Integration;

class Integration
{
    public static function getIntegrations()
    {
        return [
            'paddle'    =>  [
                'name'       => 'Paddle',
                'excerpt'    => 'Paddle provides financial services for SaaS and Digital services.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/paddle.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Payment Gateway'],
            ],
            'stripe'    => [
                'name'       => 'Stripe',
                'excerpt'    => 'Stripe is an American financial services providing company.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/stripe.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Payment Gateway'],
            ],
            'bkash' => [
                'name'       => 'bKash',
                'excerpt'    => 'bKash is a mobile financial service in Bangladesh.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/bkash.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Payment Gateway'],
            ]
        ];;
    }
}
