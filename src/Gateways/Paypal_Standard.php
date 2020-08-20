<?php

namespace SmartPay\Gateways;

use SmartPay\Payment_Gateway;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Paypal_Standard extends Payment_Gateway
{
    /** @var object|Paypal_Standard The single instance of this class */
    private static $instance = null;

    /** @var array Supported currency */
    private static $supported_currency = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'];

    /**
     * Construct Paypal class.
     *
     * @since  x.x.x
     * @access private
     */
    private function __construct()
    {
        if (!smartpay_is_gateway_active('paypal')) {
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
     * Main Paypal Instance.
     *
     * Ensures that only one instance of Paypal exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  x.x.x
     * @return object|Paypal
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Paypal)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize wp actions.
     *
     * @access private
     * @since  x.x.x
     * @return void
     */
    private function init_actions()
    {
        add_action('smartpay_paypal_process_payment', [$this, 'process_payment']);

        add_action('smartpay_paypal_ajax_process_payment', [$this, 'ajax_process_payment']);

        add_filter('smartpay_settings_sections_gateways', [$this, 'gateway_section']);

        add_filter('smartpay_settings_gateways', [$this, 'gateway_settings']);

        add_action('init', [$this, 'process_webhooks']);
    }

    /**
     * Process webhook requests.
     *
     * @since  x.x.x
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

        $payment_price = number_format($payment_data['amount'], 2);

        // TODO: Rearrange data
        $paypal_args = array(
            'charset'       => get_bloginfo('charset'),
            'lc'            => get_locale(),
            'cbt'           => get_bloginfo('name'),
            'page_style'    => 'paypal',
            'bn'            => 'WPSmartPay',
            'image_url'     => '',

            'business'      => $smartpay_options['paypal_email'],

            'cmd'           => '_cart',
            'email'         => $payment_data['email'],
            'first_name'    => $payment_data['customer']['first_name'],
            'last_name'     => $payment_data['customer']['last_name'],
            'currency_code' => $payment_data['currency'],

            'custom'        => $payment->ID,
            'invoice'       => $payment_data['key'],

            'rm'            => 2,
            'no_shipping'   => 1,
            'no_note'       => 1,

            'item_name_1'   => 'Payment #' . $payment->ID,
            'item_number_1' => $payment->ID,
            'amount_1'      => $payment_price,

            'tax_rate'      => 0,
            'upload'        => 1,

            'return'        => smartpay_get_payment_success_page_uri() . '?' . build_query([
                'payment-id' => $payment->ID
            ]),
            'cancel_return' => smartpay_get_payment_failure_page_uri() . '?' . build_query([
                'payment-id' => $payment->ID
            ]),
            'notify_url'    => get_bloginfo('url') . '/index.php?' . build_query([
                'smartpay-listener' => 'paypal',
                'payment-id' => $payment->ID
            ]),
        );

        $paypal_args = apply_filters('smartpay_paypal_redirect_args', $paypal_args, $payment_data);

        $paypal_redirect = trailingslashit($this->get_paypal_redirect_url()) . '?' . http_build_query($paypal_args);

        $content = '<p class="text-center">Redirecting to PayPal...</p>';
        $content .= '<script>window.location.replace("' . $paypal_redirect . '");</script>';

        echo $content;
        return;
    }

    /**
     * Process webhook requests.
     *
     * @since  x.x.x
     * @return void
     * @access public
     */
    public function process_webhooks()
    {
        global $smartpay_options;

        if (isset($_GET['smartpay-listener']) && sanitize_text_field($_GET['smartpay-listener']) == 'paypal') {

            // Fallback just in case post_max_size is lower than needed
            if (ini_get('allow_url_fopen')) {
                $post_data = file_get_contents('php://input');
            } else {
                // If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
                ini_set('post_max_size', '12M');
            }

            // Start the encoded data collection with notification command
            $encoded_data = 'cmd=_notify-validate';

            // Get current arg separator
            $arg_separator = ini_get('arg_separator.output');

            // Verify there is a post_data
            if ($post_data || strlen($post_data) > 0) {
                // Append the data
                $encoded_data .= $arg_separator . $post_data;
            } else {
                // Check if POST is empty
                if (empty($_POST)) {
                    // Nothing to do
                    return;
                } else {
                    // Loop through each POST
                    foreach ($_POST as $key => $value) {
                        // Encode the value and append the data
                        $encoded_data .= $arg_separator . "$key=" . urlencode($value);
                    }
                }
            }

            // Convert collected post data to an array
            parse_str($encoded_data, $encoded_data_array);

            foreach ($encoded_data_array as $key => $value) {

                if (false !== strpos($key, 'amp;')) {
                    $new_key = str_replace('&amp;', '&', $key);
                    $new_key = str_replace('amp;', '&', $new_key);

                    unset($encoded_data_array[$key]);
                    $encoded_data_array[$new_key] = $value;
                }
            }

            // Validate the IPN
            $remote_post_vars = array(
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking'    => true,
                'headers'     => array(
                    'host'         => 'www.paypal.com',
                    'connection'   => 'close',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'post'         => '/cgi-bin/webscr HTTP/1.1',
                    'user-agent'   => 'SmartPay IPN Verification/' . SMARTPAY_VERSION . '; ' . get_bloginfo('url')
                ),
                'sslverify'   => false,
                'body'        => $encoded_data_array
            );

            // Get response
            $api_response = wp_remote_post($this->get_paypal_redirect_url(true, true), $remote_post_vars);

            if (is_wp_error($api_response)) {
                return; // Something went wrong
            }

            if ('VERIFIED' !== wp_remote_retrieve_body($api_response) && $smartpay_options['disable_paypal_verification'] ?? false) {
                return; // Response not okay
            }

            // Check if $post_data_array has been populated
            if (!is_array($encoded_data_array) && !empty($encoded_data_array)) {
                return;
            }

            $defaults = array(
                'txn_type'       => '',
                'payment_status' => ''
            );

            $encoded_data_array = wp_parse_args($encoded_data_array, $defaults);

            $payment_id = absint($encoded_data_array['custom'] ?? sanitize_text_field($_GET['payment-id']) ?? 0);

            $payment = smartpay_get_payment($payment_id);

            // If payment not found
            if (!$payment) {
                echo __(sprintf(
                    'SmartPay-Paypal: Webhook requested; Smartpay payment not found for #%s.',
                    $payment_id
                ), 'smartpay');

                die('Error.');
            }

            $this->process_smartpay_paypal_web_accept($encoded_data_array, $payment);
            return;
        }
    }

    /**
     * Process web accept (one time) payment IPNs
     *
     * @since x.x.x
     * @param array $data IPN Data
     * @return void
     */
    public function process_smartpay_paypal_web_accept($data, $payment)
    {
        if ($data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded') {
            return;
        }

        // Collect payment details
        $paypal_amount  = $data['mc_gross'] ?? 0;
        $payment_status = strtolower($data['payment_status'] ?? '');

        if ($payment_status == 'refunded' || $payment_status == 'reversed') {
            // TODO: Process a refund
        } else {

            if ('publish' == $payment->status) {
                return; // Only complete payments once
            }

            if (number_format((float) $paypal_amount, 2) < number_format((float) $payment->amount, 2)) {
                return; // The prices don't match
            }

            if ('completed' == $payment_status || smartpay_is_test_mode()) {
                $payment->update_status('completed');
                smartpay_set_payment_transaction_id($payment->ID, $data['txn_id']);
            }
        }
    }

    /**
     * Add Gateway subsection
     *
     * @since  x.x.x
     * @param array $sections Gateway subsections
     * @return array
     * @access public
     */
    public function gateway_section(array $sections = array()): array
    {
        $sections['paypal'] = __('PayPal Standard', 'smartpay');

        return $sections;
    }

    /**
     * Register the gateway settings for Paypal
     *
     * @since  x.x.x
     * @param array $settings
     * @return array
     * @access public
     */
    public function gateway_settings(array $settings): array
    {
        $gateway_settings = array(
            array(
                'id'    => 'paypal_settings',
                'name'  => '<h4 class="text-uppercase text-info my-1">' . __('PayPal Settings', 'smartpay') . '</h4>',
                'desc'  => __('Configure your PayPal Standard Gateway Settings', 'smartpay'),
                'type'  => 'header'
            ),
            array(
                'id'    => 'paypal_email',
                'name'  => __('Email', 'smartpay'),
                'desc'  => __('Enter your PayPal account\'s email', 'smartpay'),
                'type'  => 'text'
            ),
            // TODO: Add url for documentation
            array(
                'id'    => 'paypal_identity_token',
                'name'  => __('PayPal Identity Token', 'smartpay'),
                'desc'  => sprintf(__('Enter your PayPal Identity Token in order to enable Payment Data Transfer (PDT). This allows payments to be verified without relying on the PayPal IPN. See our <a href="%s" target="_blank">documentation</a> for further information.', 'smartpay'), '#'),
                'type'  => 'text'
            ),
            array(
                'id'    => 'disable_paypal_verification',
                'name'  => __('Disable PayPal IPN Verification', 'smartpay'),
                'desc'  => sprintf(__('If you are unable to use Payment Data Transfer and payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases. See our <a href="%s" target="_blank">FAQ</a> for further information.', 'smartpay'), '#'),
                'type'  => 'checkbox',
            ),
            // TODO: Add url for documentation
            array(
                'id'    => 'paypal_api_keys_desc',
                'name'  => '<h4 class="text-uppercase text-info my-1">' . __('API Credentials', 'smartpay') . '</h4>',
                // 'desc'  => sprintf(__( '<p>API credentials are necessary to process PayPal refunds from inside WordPress.</p><p>These can be obtained from <a href="%s" target="_blank">your PayPal account</a>.</p>', 'smartpay' ), '#'),
                'type'  => 'descriptive_text',
            ),
            array(
                'id'    => 'paypal_live_api_settings',
                'name'  => '<strong>' . __('PayPal Live API Credentials', 'smartpay') . '</strong>',
                'type'  => 'header'
            ),
            array(
                'id'    => 'paypal_live_api_username',
                'name'  => __('Live API Username', 'smartpay'),
                'desc'  => __('Your PayPal live API username', 'smartpay'),
                'type'  => 'text',
                'size'  => 'regular',
            ),
            array(
                'id'    => 'paypal_live_api_password',
                'name'  => __('Live API Password', 'smartpay'),
                'desc'  => __('Your PayPal live API Password', 'smartpay'),
                'type'  => 'text',
                'size'  => 'regular',
            ),
            array(
                'id'    => 'paypal_live_api_signature',
                'name'  => __('Live API Signature', 'smartpay'),
                'desc'  => __('Your PayPal live API Signature', 'smartpay'),
                'type'  => 'text',
                'size'  => 'regular',
            ),

            // Test account
            array(
                'id'    => 'paypal_test_api_settings',
                'name'  => '<strong>' . __('PayPal Test API Credentials', 'smartpay') . '</strong>',
                'type'  => 'header'
            ),
            array(
                'id'    => 'paypal_test_api_username',
                'name'  => __('Test API Username', 'smartpay'),
                'desc'  => __('Your PayPal test API username', 'smartpay'),
                'type'  => 'text',
                'size'  => 'regular',
            ),
            array(
                'id'    => 'paypal_test_api_password',
                'name'  => __('Test API Password', 'smartpay'),
                'desc'  => __('Your PayPal Test API Password', 'smartpay'),
                'type'  => 'text',
                'size'  => 'regular',
            ),
            array(
                'id'    => 'paypal_test_api_signature',
                'name'  => __('Test API Signature', 'smartpay'),
                'desc'  => __('Your PayPal Test API Signature', 'smartpay'),
                'type'  => 'text',
                'size'  => 'regular',
            ),

            $paddle_webhook_description_text = __(
                sprintf(
                    '<p>For PayPal to function completely, you must configure your Instant Notification System. Visit your <a href="%s" target="_blank">account dashboard</a> to configure them. Please add the URL below to all notification types. It doesn\'t work for localhost or local IP.</p><p><b>INS URL:</b> <code>%s</code></p>.',
                    'https://paypal.com/businessmanage/preferences/website',
                    home_url("index.php?smartpay-listener=paypal")
                ),
                'smartpay'
            ),

            $_SERVER['REMOTE_ADDR'] == '127.0.0.0.1' ? $paddle_webhook_description_text .= __('<p><b>Warning!</b> It seems you are on the localhost.</p>', 'smartpay') : '',

            array(
                'id'    => 'paddle_webhook_description',
                'type'  => 'descriptive_text',
                'name'  => __('Instant Notification System (INS)', 'smartpay'),
                'desc'  => $paddle_webhook_description_text,

            ),
        );

        return array_merge($settings, ['paypal' => $gateway_settings]);
    }

    /**
     * Set and check API credentials
     *
     * @since  x.x.x
     * @return boolean
     * @access private
     */
    private function _check_credentials(): bool
    {
        global $smartpay_options;

        $paypal_email       = $smartpay_options['paypal_email'] ?? null;

        if (smartpay_is_test_mode()) {
            $api_username   = $smartpay_options['paypal_test_api_username']  ?? null;
            $api_password   = $smartpay_options['paypal_test_api_password']  ?? null;
            $api_signature  = $smartpay_options['paypal_test_api_signature'] ?? null;
        } else {
            $api_username   = $smartpay_options['paypal_live_api_username']  ?? null;
            $api_password   = $smartpay_options['paypal_live_api_password']  ?? null;
            $api_signature  = $smartpay_options['paypal_live_api_signature'] ?? null;
        }

        if (empty($paypal_email) || empty($api_username) || empty($api_password) || empty($api_signature)) {
            // TODO: Add smartpay payment error notice

            die('SmartPay-PayPal: Set credentials; You must enter your business email, api username and password for PayPal in gateway settings.');
            return false;
        }

        return true;
    }

    public function unsupported_currency_notice()
    {
        echo __('<div class="error"><p>Unsupported currency! Your currency <code>' . strtoupper(smartpay_get_currency()) . '</code> does not supported by PayPal.</p></div>', 'smartpay');
    }

    function get_paypal_redirect_url($ssl_check = false, $ipn = false)
    {
        $protocol = 'http://';
        if (is_ssl() || !$ssl_check) {
            $protocol = 'https://';
        }

        // Check the current payment mode
        if (smartpay_is_test_mode()) {

            // Test mode
            if ($ipn) {

                $paypal_uri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
            } else {

                $paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
            }
        } else {

            // Live mode
            if ($ipn) {

                $paypal_uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';
            } else {

                $paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
            }
        }

        return apply_filters('smartpay_paypal_uri', $paypal_uri, $ssl_check, $ipn);
    }
}
