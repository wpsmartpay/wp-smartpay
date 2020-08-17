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
        add_action('init', [$this, 'process_webhooks']);

        add_action('smartpay_paypal_process_payment', [$this, 'process_payment']);

        add_action('smartpay_paypal_ajax_process_payment', [$this, 'ajax_process_payment']);

        add_filter('smartpay_settings_sections_gateways', [$this, 'gateway_section']);

        add_filter('smartpay_settings_gateways', [$this, 'gateway_settings']);
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

            'return'        => smartpay_get_payment_success_page_uri(),
            'cancel_return' => get_bloginfo('url'),
            'notify_url'    => $this->get_webhook_url($payment->ID),
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
        if (isset($_GET['smartpay-listener']) && sanitize_text_field($_GET['smartpay-listener']) == 'Paypal') {

            $signature     = sanitize_text_field($_POST['p_signature']);
            $web_hook_data = stripslashes_deep($_POST);
            $identifier    = sanitize_text_field($_GET['identifier']) ?? null;
            $alert_name    = $web_hook_data['alert_name'] ?? null;
            $alert_id      = $web_hook_data['alert_id'] ?? 'N/A';
            $payment_id    = absint($web_hook_data['passthrough'] ?? sanitize_text_field($_GET['payment-id']) ?? null);

            $responsible_alerts = [
                'payment_succeeded'
            ];

            // If not responsible for regular payment then return
            if (!in_array($alert_name, $responsible_alerts) && ($identifier != $this->fulfillment_webhook_identifier)) {
                return;
            }

            $payment = smartpay_get_payment($payment_id);

            // If payment id not found
            if (!$payment) {
                echo __(sprintf(
                    'SmartPay-Paypal: Webhook requested [%s]; Smartpay payment not found for #%s.',
                    $alert_id,
                    $payment->ID
                ), 'smartpay');

                die('Error.');
            }

            if (!$this->_check_credentials()) {
                echo __(sprintf(
                    'SmartPay-Paypal: Webhook requested [%s]; Payment #%s. API credentials not properly configured.',
                    $alert_id,
                    $payment->ID
                ), 'smartpay');

                die('Error.');
            }

            global $smartpay_options;

            $public_key = $smartpay_options['Paypal_public_key'] ?? null;

            if (empty($signature) || !count($web_hook_data) || empty($public_key)) {
                echo __(sprintf(
                    'SmartPay-Paypal: Webhook requested [%s]; Signature, Webhook data or Public key can not be empty. Payment #%s.',
                    $alert_id,
                    $payment->ID
                ), 'smartpay');

                die('Error.');
            }

            try {
                $verify_signature = PaypalSDKVerify::webHookSignature($signature, $web_hook_data, $public_key);

                if ($verify_signature) {
                    if (true == $verify_signature['success']) {

                        // Fulfillment webhooks.
                        if ($identifier == $this->fulfillment_webhook_identifier) {
                            /* Sent when a one-time purchase order is processed for a product with webhook fulfillment enabled */

                            if ($payment->update_status('completed')) {
                                // Paypal transaction id.
                                $Paypal_order_id = sanitize_text_field($web_hook_data['p_order_id'] ?? null);

                                if ($Paypal_order_id) {
                                    smartpay_set_payment_transaction_id($payment->ID, $Paypal_order_id);
                                }

                                // $payment->add_note(__('Payment completed by Paypal fulfillment webhook.', 'smartpay'));

                                echo __(sprintf(
                                    'SmartPay-Paypal: Fulfillment webhook requested [%s]; Payment #%s completed.',
                                    $alert_id,
                                    $payment->ID
                                ), 'smartpay');

                                die('Success.');
                            } else {
                                // $payment->add_note(__('Payment can not completed by Paypal fulfillment webhook.', 'smartpay'));

                                echo __(sprintf(
                                    'SmartPay-Paypal: Fulfillment webhook requested [%s]; Payment #%s can not complete.',
                                    $alert_id,
                                    $payment->ID
                                ), 'smartpay');

                                die('Error.');
                            }
                        } else {
                            // Other Webhooks.

                            // Paypal transaction id.
                            $Paypal_order_id = sanitize_text_field($web_hook_data['order_id'] ?? null);

                            switch (strtolower($alert_name)) {

                                case 'payment_succeeded':
                                    /* Fired when a payment is made into your Paypal account. */

                                    if ('publish' == smartpay_get_payment_status($payment->ID)) {
                                        echo __(sprintf(
                                            'SmartPay-Paypal: Webhook requested [%s]; Payment #%s was completed before.',
                                            $alert_id,
                                            $payment->ID
                                        ), 'smartpay');

                                        die('Success.');
                                    }

                                    if ($payment->update_status('completed')) {
                                        if ($Paypal_order_id) {
                                            smartpay_set_payment_transaction_id($payment->ID, $Paypal_order_id);
                                        }

                                        // $payment->add_note(__('Payment completed by Paypal webhook.', 'smartpay'));

                                        echo __(sprintf(
                                            'SmartPay-Paypal: Webhook requested [%s]; Payment #%s completed.',
                                            $alert_id,
                                            $payment->ID
                                        ), 'smartpay');

                                        die('Success.');
                                    } else {
                                        // $payment->add_note(__('Payment can not completed by Paypal webhook.', 'smartpay'));

                                        echo __(sprintf(
                                            'SmartPay-Paypal: Webhook requested [%s]; Payment #%s can not completed.',
                                            $alert_id,
                                            $payment->ID
                                        ), 'smartpay');

                                        die('Error.');
                                    }
                                    break;

                                default:
                                    echo __(sprintf(
                                        'SmartPay-Paypal: Webhook requested [%s]; No action taken for payment #%s.',
                                        $alert_id,
                                        $payment->ID
                                    ), 'smartpay');

                                    die('Error.');
                                    break;
                            }
                        }
                    } else {
                        // If signature is not valid
                        echo __(sprintf(
                            'SmartPay-Paypal: Webhook requested [%s]; Payment #%s. Webhook signature invalid. Errors: %s',
                            $alert_id,
                            $payment->ID,
                            json_encode($verify_signature['error']['message'])
                        ), 'smartpay');

                        die('Error.');
                    }
                } else {
                    // Other errors
                    echo __(sprintf(
                        'SmartPay-Paypal: Webhook requested [%s]; Payment #%s. Something went wrong! Error on checking Webhook signature.',
                        $alert_id,
                        $payment->ID
                    ), 'smartpay');

                    die('Error.');
                }
            } catch (Exception $e) {
                // If fail to verify signature.
                echo __(sprintf(
                    'SmartPay-Paypal: Webhook requested [%s]; Payment #%s. Exception: %s.',
                    $alert_id,
                    $payment->ID,
                    var_dump($e)
                ), 'smartpay-woo');

                die('Error.');
            }

            // Send responce.
            die();
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

            die('SmartPay-PayPal: Set credentials; You must enter your vendor id, auth codes and public key for Paypal in gateway settings.');
            return false;
        }

        return true;
    }

    public function unsupported_currency_notice()
    {
        echo __('<div class="error"><p>Unsupported currency! Your currency <code>' . strtoupper(smartpay_get_currency()) . '</code> does not supported by PayPal.</p></div>', 'smartpay');
    }

    public function get_webhook_url($payment_id)
    {
        return get_bloginfo('url') . '/index.php?' . build_query(array(
            'smartpay-listener' => 'paypal',
            'identifier'        => 'fulfillment-webhook',
            'payment-id'        => $payment_id
        ));
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