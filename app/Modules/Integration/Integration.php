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
                'name'       => __('MailChimp', 'smartpay'),
                'excerpt'    => __('Mailchimp is an email marketing service.', 'smartpay'),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/mailchimp.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],
            'fluentcrm' => [
                'name'       => __('Fluent CRM', 'smartpay'),
                'excerpt'    => __('Fluent crm is an email marketing automation service.', 'smartpay'),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/fluent-crm.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],
            'affiliate_wp' => [
                'name'       => __('AffiliateWP', 'smartpay'),
                'excerpt'    => __('AffiliateWP is an affiliate marketing tool for wordpress.', 'smartpay'),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/affiliate_wp.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
            ],
            'pabbly' => [
                'name'       => __('Pabbly', 'smartpay'),
                'excerpt'    => __('Pabbly is a tool that takes care of sales & marketing needs..', 'smartpay'),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/pabbly.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing', 'Sales', 'Automation'],
            ],
            'zapier' => [
                'name'       => __('Zapier', 'smartpay'),
                'excerpt'    => __('Zapier is a tool of easy automation for busy people.', 'smartpay'),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/zapier.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Automation'],
            ],
            'mailerlite' => [
                'name'       => __('MailerLite', 'smartpay'),
                'excerpt'    => __('MailerLite is an email marketing tool.', 'smartpay'),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/mailerlite.png',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing'],
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
            if (!class_exists($integration['manager'])) {
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
        if (!isset($_POST['payload']['nonce']) || !wp_verify_nonce($_POST['payload']['nonce'], 'smartpay_integrations_toggle_activation')) {
            echo 'Invlid request';
            die();
        }

        $action    = sanitize_text_field($_POST['payload']['action'] ?? '');
        $namespace = sanitize_text_field($_POST['payload']['namespace'] ?? '');

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
