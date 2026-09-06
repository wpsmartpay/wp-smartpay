<?php

namespace SmartPay\Modules\Integration;
defined('ABSPATH') || exit;

class Integration
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('init', [$this, 'bootIntegrations'], 10);

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

        add_action('wp_ajax_smartpay_toggle_integration_activation', [$this, 'toggleIntegrationActivation']);
    }

    public static function getIntegrations()
    {
        return [
            'products'  => [
                'name'       => __( 'Products', 'smartpay' ),
                'excerpt'    => __( 'Sell digital products and downloads directly from your WordPress site.', 'smartpay' ),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/products.png',
                'manager'    => Products::class,
                'type'       => 'free',
                'categories' => [ 'Core' ],
            ],
            'legacy_forms' => [
                'name'       => __( 'Legacy Forms', 'smartpay' ),
                'excerpt'    => __( 'Enable the legacy form builder for forms created before the native form builder.', 'smartpay' ),
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/legacy-forms.png',
                'manager'    => LegacyForms::class,
                'type'       => 'free',
                'categories' => [ 'Core' ],
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

            // ── Pro integrations ────────────────────────────────────────────
            // Listed here so the free plugin advertises the whole catalogue
            // rather than the handful it happened to know about. `manager =>
            // null` is what marks an entry as not installed: the Integrations
            // screen renders it locked, with an Upgrade to Pro link and no
            // activation toggle. When the pro plugin is active it replaces each
            // of these through the `smartpay_integrations` filter with the real
            // config and manager class, so nothing here needs to change.
            'slack' => [
                'name'       => 'Slack',
                'excerpt'    => 'Get instant Slack channel notifications when payments are received, fail, or are cancelled.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/slack.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Communication'],
            ],
            'telegram' => [
                'name'       => 'Telegram',
                'excerpt'    => 'Send Telegram bot messages to any chat or channel when payment events occur.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/telegram.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Communication'],
            ],
            'twilio' => [
                'name'       => 'Twilio SMS',
                'excerpt'    => 'Send SMS notifications to customers and admins on payment events via Twilio.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/twilio.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Communication'],
            ],
            'google_sheets' => [
                'name'       => 'Google Sheets',
                'excerpt'    => 'Automatically log payment data to a Google Sheet via a Google Apps Script webhook.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/google-sheets.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Communication'],
            ],
            'wp_user_registration' => [
                'name'       => 'WP User Registration',
                'excerpt'    => 'Automatically create a WordPress account for customers after payment — no extra plugin needed.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/wp-user-registration.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Automation'],
            ],
            'fluent_support' => [
                'name'       => 'Fluent Support',
                'excerpt'    => 'Automatically open a helpdesk ticket in Fluent Support whenever a payment is completed or fails.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/fluent-support.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Support'],
            ],
            'fluent_community' => [
                'name'       => 'FluentCommunity',
                'excerpt'    => 'Automatically add customers to FluentCommunity spaces when they complete a payment.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/fluent-community.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Community'],
            ],
            'learndash' => [
                'name'       => 'LearnDash',
                'excerpt'    => 'Automatically enroll customers in LearnDash courses when they complete a payment.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/learndash.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['LMS'],
            ],
            'tutorlms' => [
                'name'       => 'Tutor LMS',
                'excerpt'    => 'Automatically enroll customers in Tutor LMS courses when they complete a payment.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/tutorlms.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['LMS'],
            ],
            'lifterlms' => [
                'name'       => 'LifterLMS',
                'excerpt'    => 'Automatically enroll customers in LifterLMS courses when they complete a payment.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/lifterlms.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['LMS'],
            ],
            'activecampaign' => [
                'name'       => 'ActiveCampaign',
                'excerpt'    => 'Sync customers to ActiveCampaign on payment — subscribe to lists, apply tags, and trigger automations automatically.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/activecampaign.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Marketing', 'CRM'],
            ],
            'integrately' => [
                'name'       => 'Integrately',
                'excerpt'    => 'Integrately connects SmartPay to 1,200+ apps in one click to automate your payment workflows.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/integrately.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Automation'],
            ],
            'wishlist_member' => [
                'name'       => 'Wishlist Member',
                'excerpt'    => 'Automatically add customers to Wishlist Member membership levels when a payment is completed.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/wishlist-member.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Membership'],
            ],
            'restrict_content_pro' => [
                'name'       => 'Restrict Content Pro',
                'excerpt'    => 'Automatically create Restrict Content Pro memberships when a customer completes a payment.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/restrict-content-pro.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Membership'],
            ],
            'uncanny_automator' => [
                'name'       => 'Uncanny Automator',
                'excerpt'    => 'Connect SmartPay payment events to 200+ WordPress plugins and 1,000+ apps via Uncanny Automator recipes.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/uncanny-automator.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Automation'],
            ],
            'wpfunnels' => [
                'name'       => 'WPFunnels',
                'excerpt'    => 'Send customers to a WPFunnels thank-you page or upsell page after completing a SmartPay payment.',
                'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/wpfunnels.svg',
                'manager'    => null,
                'type'       => 'pro',
                'categories' => ['Automation'],
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
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'smartpay')], 403);
            return;
        }

		$nonce = isset($_POST['payload']['nonce']) ? sanitize_text_field(wp_unslash($_POST['payload']['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'smartpay_integrations_toggle_activation')) {
            wp_send_json_error(['message' => __('Invalid request.', 'smartpay')], 403);
            return;
        }

        $action    = isset($_POST['payload']['action']) ? sanitize_text_field(wp_unslash($_POST['payload']['action'])) : '';
        $namespace = isset($_POST['payload']['namespace']) ? sanitize_text_field(wp_unslash($_POST['payload']['namespace'])) : '';

        if (!in_array($namespace, array_keys(smartpay_integrations()), true)) {
            wp_send_json_error(['message' => __('Invalid integration.', 'smartpay')], 400);
            return;
        }

        if ('activate' === $action) {
            $this->activateIntegration($namespace);
        } else {
            $this->deactivateIntegration($namespace);
        }
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
        wp_send_json_success(['message' => __('Integration activated.', 'smartpay')]);
    }

    private function deactivateIntegration(string $integration)
    {
        global $smartpay_options;

        if (!in_array($integration, array_keys($smartpay_options['integrations']), true)) {
            $smartpay_options['integrations'][$integration] = [
                'active'   => false,
                'settings' => []
            ];
        } else {
            $smartpay_options['integrations'][$integration]['active'] = false;
        }

        smartpay_update_settings($smartpay_options);
        wp_send_json_success(['message' => __('Integration deactivated.', 'smartpay')]);
    }
}
