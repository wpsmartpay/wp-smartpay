<?php

namespace SmartPay\Gateways;

use EDD_Stripe_API;
use EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found;
use SmartPay\Payment_Gateway;
use \Stripe\Stripe as StripeSDK;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('SMARTPAY_STRIPE_VERSION', '2.7.4');
define('SMARTPAY_STRIPE_API_VERSION', '2019-08-14');
define('SMARTPAY_STRIPE_PARTNER_ID', '');

final class Stripe extends Payment_Gateway
{
    /** @var object|Stripe The single instance of this class */
    private static $instance = null;

    /** @var array Supported currency */
    private static $supported_currency = ['USD', 'AED', 'AUD', 'BGN', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'INR', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PLN', 'RON', 'SEK', 'SGD'];

    /**
     * Construct Stripe class.
     *
     * @since  0.0.5
     * @access private
     */
    private function __construct()
    {
        if (!smartpay_is_gateway_active('stripe')) {
            return;
        }

        if (!in_array(strtoupper(smartpay_get_currency()), self::$supported_currency)) {
            add_action('admin_notices', [$this, 'unsupported_currency_notice']);
            return;
        }

        // Initialize actions.
        $this->init_actions();
    }

    /**
     * Main Stripe Instance.
     *
     * Ensures that only one instance of Stripe exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  0.0.5
     * @return object|Stripe
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Stripe)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize wp actions.
     *
     * @access private
     * @since  0.0.5
     * @return void
     */
    private function init_actions()
    {
        add_action('smartpay_stripe_process_payment', [$this, 'process_payment']);

        add_action('smartpay_stripe_ajax_process_payment', [$this, 'ajax_process_payment']);

        add_filter('smartpay_settings_sections_gateways', [$this, 'gateway_section']);

        add_filter('smartpay_settings_gateways', [$this, 'gateway_settings']);

        add_action('init', [$this, 'process_webhooks']);

        add_action('wp_enqueue_scripts', [$this, 'stripe_scripts']);

        add_action('wp_ajax_smartpay_stripe_make_payment', [$this, 'make_payment']);
        add_action('wp_ajax_nopriv_smartpay_stripe_make_payment', [$this, 'make_payment']);
    }

    /**
     * Process webhook requests.
     *
     * @since  0.0.5
     * @param array $payment_data
     * @return void
     * @access public
     */
    public function process_payment($payment_data)
    {
        return;
    }

    public function ajax_process_payment($payment_data)
    {
        global $smartpay_options;

        if (!$this->_check_credentials()) {
            // TODO: Implement smartpay_set_error

            die('Credentials error.');
            wp_redirect(get_permalink($smartpay_options['payment_failure_page']), 302);
        }

        $payment = smartpay_insert_payment($payment_data);

        if (!$payment->ID) {
            die('Can\'t insert payment.');
            wp_redirect(get_permalink($smartpay_options['payment_failure_page']), 302);
        }

        $this->cc_form($payment);
    }

    private function cc_form($payment)
    {
        $content = '<form action="#" method="post" id="stripe-payment-form" class="p-1">
            <div class="form-group StripeElement">
                <input type="text" placeholder="Card holder name" name="stripe_card_holder" id="stripe_card_holder" style="font-size: 16px; padding: 0; line-height: 19px; height: 19px; border: 0 !important;">
            </div>
            <div id="card-element"></div>
            <div id="card-errors" role="alert"></div>
            <input type="hidden" name="smartpay_payment_id" id="" value="' . $payment->ID . '">

            <button class="btn-success btn-block btn-lg smartpay-stripe-payment" style="margin-top: 20px;font-size: 16px;padding: 6px 10px;border-radius: 4px;">' . __('Submit Payment', 'smartpay') . '</button>
        </form>';

        $content .= '<script type="text/javascript" src="' . plugins_url('/assets/js/gateways/stripe.js', SMARTPAY_PLUGIN_FILE) . '"></script>';
        echo $content;

        return;
    }

    public function make_payment()
    {
        $secret_key = smartpay_is_test_mode() ? smartpay_get_option('stripe_test_secret_key') : smartpay_get_option('stripe_live_secret_key');
        $data = $_POST['data'];

        StripeSDK::setApiKey(trim($secret_key));
        $payment = smartpay_get_payment($data['smartpay_payment_id']);

        // Token is created using Stripe Checkout or Elements!
        $charge = \Stripe\Charge::create([
            'amount' => $payment->amount * 100,
            'currency' => smartpay_get_currency(),
            'source' => $data['stripeToken'],
        ]);

        if ($charge->id) {
            $payment->update_status('completed');

            echo json_encode([
                'status' => true, 'redirect_to' => add_query_arg(['payment-id' => $data['smartpay_payment_id']], smartpay_get_payment_success_page_uri())
            ]);
            exit;
        }

        echo json_encode($charge);
        exit;
    }

    /**
     * Process webhook requests.
     *
     * @since  0.0.5
     * @return void
     * @access public
     */
    public function process_webhooks()
    {
        if (isset($_GET['smartpay-listener']) && sanitize_text_field($_GET['smartpay-listener']) == 'stripe') {
            return;
        }
    }

    /**
     * Add Gateway subsection
     *
     * @since  0.0.5
     * @param array $sections Gateway subsections
     * @return array
     * @access public
     */
    public function gateway_section(array $sections = array()): array
    {
        $sections['stripe'] = __('Stripe', 'smartpay');

        return $sections;
    }

    /**
     * Register the gateway settings for stripe
     *
     * @since  0.0.5
     * @param array $settings
     * @return array
     * @access public
     */
    public function gateway_settings(array $settings): array
    {
        $gateway_settings = array(
            array(
                'id'    => 'stripe_settings',
                'name'  => '<h4 class="text-uppercase text-info my-1">' . __('Stripe Settings', 'smartpay') . '</h4>',
                'desc'  => __('Configure your Stripe Standard Gateway Settings', 'smartpay'),
                'type'  => 'header'
            ),
            array(
                'id'   => 'stripe_test_publishable_key',
                'name'  => __('Test Publishable Key', 'smartpay'),
                'desc'  => __('Enter your test publishable key, found in your Stripe Account Settings', 'smartpay'),
                'type'  => 'text',
            ),
            array(
                'id'   => 'stripe_test_secret_key',
                'name'  => __('Test Secret Key', 'smartpay'),
                'desc'  => __('Enter your test secret key, found in your Stripe Account Settings', 'smartpay'),
                'type'  => 'text',
            ),
            array(
                'id'   => 'stripe_live_publishable_key',
                'name'  => __('Live Publishable Key', 'smartpay'),
                'desc'  => __('Enter your live publishable key, found in your Stripe Account Settings', 'smartpay'),
                'type'  => 'text',
            ),
            array(
                'id'   => 'stripe_live_secret_key',
                'name'  => __('Live Secret Key', 'smartpay'),
                'desc'  => __('Enter your live secret key, found in your Stripe Account Settings', 'smartpay'),
                'type'  => 'text',
            ),
            $stripe_webhook_description_text = __(
                sprintf(
                    '<p>For stripe to function completely, you must configure your Instant Notification System. Visit your <a href="%s" target="_blank">account dashboard</a> to configure them. Please add the URL below to all notification types. It doesn\'t work for localhost or local IP.</p><p><b>INS URL:</b> <code>%s</code></p>',
                    'https://dashboard.stripe.com/account/webhooks',
                    home_url("index.php?smartpay-listener=stripe")
                ),
                'smartpay'
            ),

            $_SERVER['REMOTE_ADDR'] == '127.0.0.0.1' ? $stripe_webhook_description_text .= __('<p><b>Warning!</b> It seems you are on the localhost.</p>', 'smartpay') : '',

            array(
                'id'    => 'stripe_webhook_description',
                'type'  => 'descriptive_text',
                'name'  => __('Instant Notification System (INS)', 'smartpay'),
                'desc'  => $stripe_webhook_description_text,

            ),
        );

        return array_merge($settings, ['stripe' => $gateway_settings]);
    }

    public function stripe_scripts()
    {
        $publishable_key = smartpay_is_test_mode() ? smartpay_get_option('stripe_test_publishable_key') : smartpay_get_option('stripe_live_publishable_key');

        wp_register_script('stripe-js', 'https://js.stripe.com/v3/', ['jquery'], SMARTPAY_VERSION);
        // wp_register_script('stripe-checkout', 'https://checkout.stripe.com/checkout.js', ['jquery'], SMARTPAY_VERSION);

        // wp_enqueue_script('stripe-js');
        // wp_enqueue_script('jQuery.payment');

        wp_localize_script(
            'stripe-js',
            'smartpay_stripe',
            ['publishable_key' => trim($publishable_key),]
        );


        wp_enqueue_script('stripe-js');
    }

    /**
     * Set and check API credentials
     *
     * @since  0.0.5
     * @return boolean
     * @access private
     */
    private function _check_credentials(): bool
    {
        return true;
    }

    private function _api_request($object, $method, $args = null)
    {
        $this->_check_credentials();

        $secret_key = smartpay_is_test_mode() ? smartpay_get_option('test_secret_key') : smartpay_get_option('live_secret_key');

        StripeSDK::setApiKey(trim($secret_key));

        StripeSDK::setAppInfo(
            'WPSmartPay',
            SMARTPAY_STRIPE_VERSION,
            esc_url(site_url()),
            SMARTPAY_STRIPE_PARTNER_ID
        );

        StripeSDK::setApiVersion(SMARTPAY_STRIPE_API_VERSION);

        $classname = 'Stripe\\' . $object;

        // Retrieve additional arguments.
        $args = func_get_args();
        unset($args[0]); // Removes $object.
        unset($args[1]); // Removes $method.

        // Reset keys.
        $args = array_values($args);

        if (!is_callable(array($classname, $method))) {
            throw new EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found(sprintf(esc_html__('Unable to call %1$s::%2$s', 'edds'), $classname, $method));
        }

        return call_user_func_array(array($classname, $method), $args);
    }

    public function unsupported_currency_notice()
    {
        echo __('<div class="error"><p>Unsupported currency! Your currency <code>' . strtoupper(smartpay_get_currency()) . '</code> does not supported by Stripe.</p></div>', 'smartpay');
    }
}
