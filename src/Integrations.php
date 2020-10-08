<?php

namespace SmartPay;

// Exit if accessed directly.
defined('ABSPATH') || exit;

final class Integrations
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Integrations class.
     *
     * @since 0.0.1
     */
    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'boot_integrations'], 999);
    }

    /**
     * Main Integrations Instance.
     *
     * Ensures that only one instance of Integrations exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     *
     * @return object|Integrations
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Integrations)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function integrations()
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
        ];
    }

    public function boot_integrations()
    {
        foreach (smartpay_active_integrations() as $namespace => $integration) {
            if (!class_exists($integration['manager'])) {
                continue;
            }

            smartpay_integration_get_manager($integration['manager'])->boot();

            do_action('smartpay_integration_' . strtolower($namespace) . '_loaded');
        }

        do_action('smartpay_integrations_loaded');
    }
}