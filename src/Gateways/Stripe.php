<?php

namespace SmartPay\Gateways;

use SmartPay\Payment_Gateway;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
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
        return;
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
        );

        return array_merge($settings, ['stripe' => $gateway_settings]);
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

    public function unsupported_currency_notice()
    {
        echo __('<div class="error"><p>Unsupported currency! Your currency <code>' . strtoupper(smartpay_get_currency()) . '</code> does not supported by Stripe.</p></div>', 'smartpay');
    }
}