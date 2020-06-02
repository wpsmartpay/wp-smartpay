<?php

namespace SmartPay\Gateways;

use SmartPay\Payment_Gateway;
use ThemeXpert\Paddle\Paddle as PaddleSDK;
use ThemeXpert\Paddle\Util\Price as PaddleSDKPrice;
use ThemeXpert\Paddle\Product\PayLink as PaddleSDKPayLink;
use ThemeXpert\Paddle\Verify as PaddleSDKVerify;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Paddle extends Payment_Gateway
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    private static $supported_currency = ['USD', 'EUR', 'GBP', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'HKD', 'HUF', 'INR', 'JPY', 'KRW', 'MXN', 'NZD', 'PLN', 'RUB', 'SEK', 'SGD', 'TWD', 'ZAR'];

    /**
     * Construct Paddle class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        if (!smartpay_is_gateway_active('paddle')) {
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
     * Main Paddle Instance.
     *
     * Ensures that only one instance of Paddle exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Paddle
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Paddle)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize wp actions.
     *
     * @access private
     * @since 0.1
     * @return void
     */
    private function init_actions()
    {
        add_action('init', [$this, 'process_webhooks']);

        add_action('smartpay_paddle_process_payment', [$this, 'process_payment']);

        add_action('smartpay_paddle_ajax_process_payment', [$this, 'ajax_process_payment']);

        add_filter('smartpay_settings_sections_gateways', [$this, 'gateway_section']);

        add_filter('smartpay_settings_gateways', [$this, 'gateway_settings']);

        add_filter('smartpay_payment_paddle_receipt', [$this, 'payment_receipt']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_payment_scripts']);
    }

    /**
     * Process webhook requests.
     *
     * @since 0.1
     * @param array $payment_data
     * @return void
     * @access public
     */
    public function process_payment($payment_data)
    {
        // var_dump($payment_data);
        // exit;

        global $smartpay_options;

        if (!$this->_set_credentials()) {
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

        $pay_link_data = array(
            'title'             => 'Payment #' . $payment->ID,
            'customer_email'    => $payment_data['email'],
            'passthrough'       => $payment->ID,
            'prices'            => [(string) new PaddleSDKPrice($payment_data['currency'], $payment_price)],
            'quantity' => 1,
            'quantity_variable' => 0,
            'discountable'      => 0,
            'return_url'        => get_permalink(smartpay_get_payment_success_page_uri()),
            'webhook_url'       => get_bloginfo('url') . '/index.php?' . build_query(array(
                'smartpay-listener' => 'paddle',
                'identifier'        => 'fulfillment-webhook',
                'payment-id'        => $payment->ID
            )),
        );

        // API request to create pay link
        $api_response_data = json_decode(PaddleSDKPayLink::create($pay_link_data));

        // If Paylink created successfully
        if ($api_response_data && $api_response_data->success == true) {
            update_post_meta($payment->ID, 'paddle_pay_link', $api_response_data->response->url);

            $checkout_location = $smartpay_options['paddle_checkout_location'] ?? 'popup';

            if ($checkout_location == 'paddle_checkout') {
                return wp_redirect($api_response_data->response->url, 302);
            } else {
                return wp_redirect(smartpay_get_payment_success_page_uri(), 302);
            }
        } else {
            die('API response error.');
        }

        return wp_redirect(smartpay_get_payment_failure_page_uri(), 302);
    }

    public function ajax_process_payment()
    {
        echo 'paddle';
        $content = '';
        $content .= '<p>' . __(
            'Thank you for your order, please click the button below to pay with Paddle.',
            'smartpay'
        ) . '</p>';
        $content .= '<div style="margin: 0 auto;text-align: center;">';
        $content .= sprintf('<a href="#!" class="paddle_button button alt" data-override="%s">Pay Now!</a>', 'aaa');
        $content .= '</div>';

        $content .= '<script>jQuery.getScript("https://cdn.paddle.com/paddle/paddle.js", function(){';
        $content .= 'Paddle.Setup({';
        $content .= sprintf('vendor: %s', '111');
        $content .= ' });';

        // Open popup on page load
        // $content .= 'Paddle.Checkout.open({';
        // $content .= sprintf('override: "%s"', 'aaa');
        // $content .= '});';

        $content .= '});';
        $content .= '</script>';

        echo $content;
    }

    /**
     * Payment receipt.
     *
     * @since 0.1
     * @param object $payment
     * @return void
     * @access public
     */
    public function payment_receipt($payment)
    {
        if ('paddle' != $payment->gateway) {
            return;
        }

        // if($payment[])
        // $payment_id = smartpay_set_session_payment_id();

        echo $this->_pay_now_content($payment);
    }

    /**
     * Generate pay now content.
     *
     * @since 0.1
     * @param object $payment
     * @access private
     */
    private function _pay_now_content($payment)
    {
        $vendor_id = smartpay_get_option('paddle_vendor_id');

        if (empty($vendor_id)) {
            die('Credentials error.');
        }

        $paddle_pay_link = get_post_meta($payment->ID, 'paddle_pay_link', true);

        if (!$paddle_pay_link) {
            die('Paddle pay link not found.');
        }

        if ('publish' != $payment->status) {
            $content = '';
            $content .= '<p>' . __(
                'Thank you for your order, please click the button below to pay with Paddle.',
                'smartpay'
            ) . '</p>';
            $content .= '<div style="margin: 0 auto;text-align: center;">';
            $content .= sprintf('<a href="#!" class="paddle_button button alt" data-override="%s">Pay Now!</a>', $paddle_pay_link);
            $content .= '</div>';

            $content .= '<script src="https://code.jquery.com/jquery-3.5.0.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script><script type="text/javascript">';
            $content .= 'jQuery.getScript("https://cdn.paddle.com/paddle/paddle.js", function(){';
            $content .= 'Paddle.Setup({';
            $content .= sprintf('vendor: %s', $vendor_id);
            $content .= ' });';

            // Open popup on page load
            $content .= 'Paddle.Checkout.open({';
            $content .= sprintf('override: "%s"', $paddle_pay_link);
            $content .= '});';

            $content .= '});';
            $content .= '</script>';

            return $content;
        }
    }

    /**
     * Process webhook requests.
     *
     * @since 0.1
     * @return void
     * @access public
     */
    public function process_webhooks()
    {
        if (isset($_GET['smartpay-listener']) && $_GET['smartpay-listener'] == 'paddle') {

            $signature     = $_POST['p_signature'];
            $web_hook_data = stripslashes_deep($_POST);
            $identifier    = $_GET['identifier'] ?? null;
            $alert_name    = $web_hook_data['alert_name'] ?? null;
            $alert_id      = $web_hook_data['alert_id'] ?? 'N/A';
            $payment_id    = absint($web_hook_data['passthrough'] ?? $_GET['payment-id'] ?? null);

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
                    'SmartPay-Paddle: Webhook requested [%s]; Smartpay payment not found for #%s.',
                    $alert_id,
                    $payment->ID
                ), 'smartpay');

                die('Error.');
            }

            if (!$this->_set_credentials()) {
                echo __(sprintf(
                    'SmartPay-Paddle: Webhook requested [%s]; Payment #%s. API credentials not properly configured.',
                    $alert_id,
                    $payment->ID
                ), 'smartpay');

                die('Error.');
            }

            global $smartpay_options;

            $public_key = $smartpay_options['paddle_public_key'] ?? null;

            if (empty($signature) || !count($web_hook_data) || empty($public_key)) {
                echo __(sprintf(
                    'SmartPay-Paddle: Webhook requested [%s]; Signature, Webhook data or Public key can not be empty. Payment #%s.',
                    $alert_id,
                    $payment->ID
                ), 'smartpay');

                die('Error.');
            }

            try {
                $verify_signature = PaddleSDKVerify::webHookSignature($signature, $web_hook_data, $public_key);

                if ($verify_signature) {
                    if (true == $verify_signature['success']) {

                        // Fulfillment webhooks.
                        if ($identifier == $this->fulfillment_webhook_identifier) {
                            /* Sent when a one-time purchase order is processed for a product with webhook fulfillment enabled */

                            if ($payment->update_status('completed')) {
                                // Paddle transaction id.
                                $paddle_order_id = sanitize_text_field($web_hook_data['p_order_id'] ?? null);

                                if ($paddle_order_id) {
                                    smartpay_set_payment_transaction_id($payment->ID, $paddle_order_id);
                                }

                                // $payment->add_note(__('Payment completed by paddle fulfillment webhook.', 'smartpay'));

                                echo __(sprintf(
                                    'SmartPay-Paddle: Fulfillment webhook requested [%s]; Payment #%s completed.',
                                    $alert_id,
                                    $payment->ID
                                ), 'smartpay');

                                die('Success.');
                            } else {
                                // $payment->add_note(__('Payment can not completed by paddle fulfillment webhook.', 'smartpay'));

                                echo __(sprintf(
                                    'SmartPay-Paddle: Fulfillment webhook requested [%s]; Payment #%s can not complete.',
                                    $alert_id,
                                    $payment->ID
                                ), 'smartpay');

                                die('Error.');
                            }
                        } else {
                            // Other Webhooks.

                            // Paddle transaction id.
                            $paddle_order_id = sanitize_text_field($web_hook_data['order_id'] ?? null);

                            switch (strtolower($alert_name)) {

                                case 'payment_succeeded':
                                    /* Fired when a payment is made into your Paddle account. */

                                    if ('publish' == smartpay_get_payment_status($payment->ID)) {
                                        echo __(sprintf(
                                            'SmartPay-Paddle: Webhook requested [%s]; Payment #%s was completed before.',
                                            $alert_id,
                                            $payment->ID
                                        ), 'smartpay');

                                        die('Success.');
                                    }

                                    if ($payment->update_status('completed')) {
                                        if ($paddle_order_id) {
                                            smartpay_set_payment_transaction_id($payment->ID, $paddle_order_id);
                                        }

                                        // $payment->add_note(__('Payment completed by paddle webhook.', 'smartpay'));

                                        echo __(sprintf(
                                            'SmartPay-Paddle: Webhook requested [%s]; Payment #%s completed.',
                                            $alert_id,
                                            $payment->ID
                                        ), 'smartpay');

                                        die('Success.');
                                    } else {
                                        // $payment->add_note(__('Payment can not completed by paddle webhook.', 'smartpay'));

                                        echo __(sprintf(
                                            'SmartPay-Paddle: Webhook requested [%s]; Payment #%s can not completed.',
                                            $alert_id,
                                            $payment->ID
                                        ), 'smartpay');

                                        die('Error.');
                                    }
                                    break;

                                default:
                                    echo __(sprintf(
                                        'SmartPay-Paddle: Webhook requested [%s]; No action taken for payment #%s.',
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
                            'SmartPay-Paddle: Webhook requested [%s]; Payment #%s. Webhook signature invalid. Errors: %s',
                            $alert_id,
                            $payment->ID,
                            json_encode($verify_signature['error']['message'])
                        ), 'smartpay');

                        die('Error.');
                    }
                } else {
                    // Other errors
                    echo __(sprintf(
                        'SmartPay-Paddle: Webhook requested [%s]; Payment #%s. Something went wrong! Error on checking Webhook signature.',
                        $alert_id,
                        $payment->ID
                    ), 'smartpay');

                    die('Error.');
                }
            } catch (Exception $e) {
                // If fail to verify signature.
                echo __(sprintf(
                    'SmartPay-Paddle: Webhook requested [%s]; Payment #%s. Exception: %s.',
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
     * @since 0.1
     * @param array $sections Gateway subsections
     * @return array
     * @access public
     */
    public function gateway_section(array $sections = array()): array
    {
        $sections['paddle'] = __('Paddle', 'smartpay');

        return $sections;
    }

    /**
     * Register the gateway settings for Paddle
     *
     * @since 0.1
     * @param array $settings
     * @return array
     * @access public
     */
    public function gateway_settings(array $settings): array
    {
        $gateway_settings = array(
            array(
                'id'    => 'paddle_settings',
                'name'  => '<strong>' . __('Paddle Gateway Settings', 'smartpay') . '</strong>',
                'desc'  => __('Configure your Paddle Gateway Settings', 'smartpay'),
                'type'  => 'header'
            ),
            array(
                'id'    => 'paddle_vendor_id',
                'name'  => __('Vendor ID', 'smartpay'),
                'desc'  => __('Enter your Paddle Vendor ID', 'smartpay'),
                'type'  => 'text'
            ),
            array(
                'id'    => 'paddle_vendor_auth_code',
                'name'  => __('Auth Codes', 'smartpay'),
                'desc'  => __('Get Auth Code from Paddle : Developer > Authentication', 'smartpay'),
                'type'  => 'text'
            ),
            array(
                'id'    => 'paddle_public_key',
                'name'  => __('Public Key', 'smartpay'),
                'desc'  => __('Get Your Public Key – this can be found  under Developer Tools > Public Key', 'smartpay'),
                'type'  => 'textarea',
                'size'  => 'regular',
            ),
            // array(
            //     'id' => 'paddle_is_api_authenticated_content',
            //     'type' => 'custom_content',
            //     'content' => '<p id="is_api_authenticated_result" class="notice hidden is-dismissible"></p><br><button type="button" id="smartpay_paddle_check_is_api_authenticated" class="button button-primary">Check Credentials</button>',
            // ),
            // array(
            //     'id'    => 'paddle_checkout_label',
            //     'name'  => __('Gateway Title', 'smartpay'),
            //     'desc'  => __('Set a custom title for the payment page. If you don\'t set, it will use the default value.', 'smartpay'),
            //     'type'  => 'text',
            // ),
            // array(
            //     'id'    => 'paddle_checkout_icon',
            //     'name'  => __('Gateway Icon', 'smartpay'),
            //     'desc'  => __('Gateway Icon URL must be including http:// or https://. If you don\'t set, it will use the default value.', 'smartpay'),
            //     'type'  => 'upload',
            //     'size'  => 'regular',
            // ),
            // array(
            //     'id'    => 'paddle_checkout_image',
            //     'name'  => __('Checkout Image URL', 'smartpay'),
            //     'desc'  => __('Checkout Image URL must be including https://. If you don\'t set, it will use the default value.', 'smartpay'),
            //     'type'  => 'upload',
            //     'size'  => 'regular',
            // ),
            array(
                'id'    => 'paddle_checkout_location',
                'name'  => __('Checkout Location', 'smartpay'),
                'desc'  => __('Select Checkout Location', 'smartpay'),
                'type'  => 'select',
                'options'   => array(
                    'popup' => 'Popup',
                    'paddle_checkout' => 'Paddle Checkout'
                ),
                'size'  => 'regular',
                'defaultValue'  => 'popup',
            ),
            array(
                'id'    => 'paddle_checkout_location_description',
                'desc'  => __('<p><b>Warning:</b> You must set the Instant Notification System (INS) for Paddle Checkout.<br>', 'smartpay'),
                'type'  => 'descriptive_text',
            ),

            $paddle_webhook_description_text = __(
                sprintf(
                    '<p>For Paddle to function completely, you must configure your Instant Notification System. Visit your <a href="%s" target="_blank">account dashboard</a> to configure them. Please add the URL below to all notification types. It doesn\'t work for localhost or local IP.</p><p><b>INS URL:</b> <code>%s</code></p>.',
                    'https://vendors.paddle.com/alerts-webhooks',
                    home_url("index.php?smartpay-listener=paddle")
                ),
                'smartpay'
            ),

            $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? $paddle_webhook_description_text .= __('<p><b>Warning!</b> It seems you are on the localhost.</p>', 'smartpay') : '',

            array(
                'id'    => 'paddle_webhook_description',
                'type'  => 'descriptive_text',
                'name'  => __('Instant Notification System (INS)', 'smartpay'),
                'desc'  => $paddle_webhook_description_text,

            ),
        );

        return array_merge($settings, ['paddle' => $gateway_settings]);
    }

    /**
     * Set and check API credentials
     *
     * @since 0.1
     * @return boolean
     * @access private
     */
    private function _set_credentials(): bool
    {
        global $smartpay_options;

        $vendor_id          = $smartpay_options['paddle_vendor_id']         ?? null;
        $vendor_auth_code   = $smartpay_options['paddle_vendor_auth_code']  ?? null;
        $public_key         = $smartpay_options['paddle_public_key']        ?? null;

        if (empty($vendor_id) || empty($vendor_auth_code) || empty($public_key)) {
            // TODO: Add smartpay payment error notice
            die('SmartPay-Paddle: Set credentials; You must enter your vendor id, auth codes and public key for Paddle in gateway settings.');
        }

        PaddleSDK::setApiCredentials($vendor_id, $vendor_auth_code);

        return true;
    }

    public function unsupported_currency_notice()
    {
        echo __('<div class="error"><p>Unsupported currency! Your currency <code>' . strtoupper(smartpay_get_currency()) . '</code> does not supported by Paddle.</p></div>', 'smartpay');
    }

    public function enqueue_payment_scripts()
    {
        wp_register_script('smartpay-payment', plugins_url('/assets/js/payment.js', SMARTPAY_FILE), array('jquery'), SMARTPAY_VERSION);

        wp_enqueue_script('smartpay-payment');
    }
}