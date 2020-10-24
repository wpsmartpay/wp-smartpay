<?php

namespace SmartPay;

use SmartPay\Container\Container;

// Exit if accessed directly.
defined('ABSPATH') || exit;

final class Integrations extends Container
{
    /**
     * The single instance of this class.
     */
	private static $instance = null;

	private $integrationProviders = [];

    /**
     * Construct Integrations class.
     *
     * @since 0.0.1
     */
    private function __construct()
    {
		add_action('plugins_loaded', [$this, 'load_integrations'], 99);

		add_action('init', [$this, 'boot_integrations']);
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

    public function load_integrations()
    {
        foreach (smartpay_active_integrations() as $namespace => $integration) {

			$manager = $integration['manager'];

			if(!$manager) {
				continue;
			}

			if (!is_callable($manager)) {
				$manager = $this->get($manager);
			}

			if ($manager instanceof Integration) {
				array_push($this->integrationProviders, $manager);
			}
		}


		// var_dump($this->integrationProviders);
		array_walk($this->integrationProviders, function (Integration $provider) {
			$this->registerIntegration($provider);
		});

        do_action('smartpay_integrations_loaded');
	}

	public function boot_integrations()
	{
		array_walk($this->integrationProviders, function (Integration $integration) {
			$this->bootIntegration($integration);
		});
	}

	public function registerIntegration(Integration $integration)
	{
		if (method_exists($integration, 'register')) {
			call_user_func([$integration, 'register']);
		}
	}

	public function bootIntegration(Integration $integration)
	{
		if (method_exists($integration, 'boot')) {
			call_user_func([$integration, 'boot']);
		}
	}
}
