<?php

namespace SmartPay\Modules\Integration;

class Integration
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('plugins_loaded', [$this, 'bootIntegrations'], 99);

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

        add_action('wp_ajax_toggle_integration_activation', [$this, 'toggleIntegrationActivation']);
    }

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
            ],
            'razorpay' => [
                'name'       => 'Razorpay',
                'excerpt'    => 'Razorpay provides financial services for SaaS and Digital services in India.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/razorpay.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Payment Gateway'],
            ],
            'mailchimp' => [
                'name'       => 'MailChimp',
                'excerpt'    => 'Mailchimp is an email marketing service.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/mailchimp.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],
            'fluentcrm' => [
                'name'       => 'Fluent CRM',
                'excerpt'    => 'Fluent crm is an email marketing automation service.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/fluent-crm.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],
            'affiliate_wp' => [
                'name'       => 'AffiliateWP',
                'excerpt'    => 'AffiliateWP is an affiliate marketing tool for wordpress.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/affiliate_wp.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],
            'pabbly' => [
                'name'       => 'Pabbly',
                'excerpt'    => 'Pabbly is a tool that takes care of sales & marketing needs..',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/pabbly.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing', 'Sales', 'Automation'],
            ],
            'zapier' => [
                'name'       => 'Zapier',
                'excerpt'    => 'Zapier is a tool of easy automation for busy people.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/zapier.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Automation'],
            ],
            'mailerlite' => [
                'name'       => 'MailerLite',
                'excerpt'    => 'MailerLite is an email marketing tool.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/mailerlite.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],

            'mollie' => [
                'name'       => 'Mollie',
                'excerpt'    => 'Mollie is a payments platform that offers an easy-to-implement process for integrating payments.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/mollie.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Payment Gateway'],
            ],

            'toyyibpay' => [
	            'name'       => 'toyyibPay',
	            'excerpt'    => 'Quickest & easiest Malaysian online payment solution.',
	            'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/toyyibpay.png',
	            'manager'    => null,
	            'type'       => 'pro',
	            'categories' => ['Payment Gateway'],
            ],

            'paytm' => [
	            'name'       => 'Paytm',
	            'excerpt'    => 'Indian digital payments and financial services company.',
	            'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/paytm.png',
	            'manager'    => null,
	            'type'       => 'pro',
	            'categories' => ['Payment Gateway'],
            ],
        ];
    }

    public static function getIntegrationManager(string $manager)
    {
        return smartpay()->make($manager);
    }

    public function bootIntegrations()
    {
        foreach (smartpay_active_integrations() as $namespace => $integration) {
            if (is_null($integration['manager']) || !class_exists($integration['manager'])) {
                continue;
            }

            smartpay_integration_get_manager($integration['manager'])->boot();

            do_action('smartpay_integration_' . strtolower($namespace) . '_loaded');
        }

        do_action('smartpay_integrations_loaded');
    }

    public function adminScripts($hook)
    {
        if ('smartpay_page_smartpay-integrations' === $hook) {
            wp_register_script('smartpay-admin-integration', SMARTPAY_PLUGIN_ASSETS . '/js/integration.js', ['jquery'], SMARTPAY_VERSION, true);
            wp_enqueue_script('smartpay-admin-integration');

            wp_localize_script(
                'smartpay-admin-integration',
                'smartpay',
                array(
                    'restUrl'  => get_rest_url('', 'smartpay'),
                    'adminUrl'  => admin_url('admin.php'),
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'apiNonce' => wp_create_nonce('wp_rest'),
                )
            );
        }
    }

    public function toggleIntegrationActivation()
    {
		$nonce = isset($_POST['payload']['nonce']) ? sanitize_text_field(wp_unslash($_POST['payload']['nonce'])): '';
        if (!$nonce || !wp_verify_nonce($nonce, 'smartpay_integrations_toggle_activation')) {
            echo 'Invlid request';
            die();
        }

        $action    = isset($_POST['payload']['action']) ? sanitize_text_field(wp_unslash($_POST['payload']['action'])) : '';
        $namespace = isset($_POST['payload']['namespace']) ? sanitize_text_field(wp_unslash($_POST['payload']['namespace'])) : '';

        if (!in_array($namespace, array_keys(smartpay_integrations()))) {
            echo 'Requested for invalid integration';
            die();
        }

        if ('activate' === $action) {
            $this->activateIntegration($namespace);
        } else {
            $this->deactivateIntegration($namespace);
        }

        die(); // Must terminate the api/ajax request
    }

    private function activateIntegration(string $integration)
    {
        global $smartpay_options;

        if (!is_array($smartpay_options['integrations'])) {
            $smartpay_options['integrations'] = [];
        }

        if (!in_array($integration, array_keys($smartpay_options['integrations']))) {
            $smartpay_options['integrations'][$integration] = [
                'active'   => true,
                'settings' => []
            ];
        } else {
            $smartpay_options['integrations'][$integration]['active'] = true;
        }

        smartpay_update_settings($smartpay_options);
        echo 'Activated';
    }

    private function deactivateIntegration(string $integration)
    {
        global $smartpay_options;

        if (!in_array($integration, array_keys($smartpay_options['integrations']))) {
            $smartpay_options['integrations'][$integration] = [
                'active'   => false,
                'settings' => []
            ];
        } else {
            $smartpay_options['integrations'][$integration]['active'] = false;
        }

        smartpay_update_settings($smartpay_options);
        echo 'Disabled';
    }
}
