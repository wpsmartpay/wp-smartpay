<?php

namespace ThemesGrove\SmartPay\Gateways;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Paddle extends SmartPayPaymentGateway
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Paddle class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'process_webhooks']);
        add_action('smartpay_paddle_process_payment', [$this, 'process_payment']);
        add_filter('smartpay_settings_sections_gateways', [$this, 'gateway_section']);
        add_filter('smartpay_settings_gateways', [$this, 'gateway_settings']);
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
     * Process webhook requests.
     *
     * @since 1.1.0
     * @return void
     * @access public
     */
    public function process_webhooks()
    {
        if (isset($_GET['smartpay-listener']) && $_GET['smartpay-listener'] == 'paddle') {
            echo 'paddle webhook';
            die();
        }
    }

    public function process_payment($payment_data)
    {
        // $payment_data

        $payment_id = smartpay_insert_payment($payment_data);

        if ($payment_id) {
            $redirect_uri = smartpay_get_success_page_uri();
            wp_redirect($redirect_uri);
        } else {
            die('Error');
        }
    }

    /**
     * Add Gateway subsection
     *
     * @since 1.0.0
     * @param array $sections Gateway subsections
     * @return array
     * @access public
     */
    public function gateway_section(array $sections = array()): array
    {
        $sections['paddle'] = __('Paddle', 'wp-smartpay');

        return $sections;
    }


    /**
     * Register the gateway settings for Paddle
     *
     * @since 1.1.0
     * @param array $settings
     * @return array
     * @access public
     */
    public function gateway_settings(array $settings): array
    {
        $gateway_settings = array(
            array(
                'id'    => 'paddle_settings',
                'name'  => '<strong>' . __('Paddle Gateway Settings', 'wp-smartpay') . '</strong>',
                'desc'  => __('Configure your Paddle Gateway Settings', 'wp-smartpay'),
                'type'  => 'header'
            ),

            array(
                'id'    => 'paddle_vendor_id',
                'name'  => __('Vendor ID', 'wp-smartpay'),
                'desc'  => __('Enter your Paddle Vendor ID', 'wp-smartpay'),
                'type'  => 'text'
            ),
            array(
                'id'    => 'paddle_vendor_auth_code',
                'name'  => __('Auth Codes', 'wp-smartpay'),
                'desc'  => __('Get Auth Code from Paddle : Developer > Authentication', 'wp-smartpay'),
                'type'  => 'text'
            ),
            array(
                'id'    => 'paddle_public_key',
                'name'  => __('Public Key', 'wp-smartpay'),
                'desc'  => __('Get Your Public Key – this can be found  under Developer Tools > Public Key', 'wp-smartpay'),
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
            //     'name'  => __('Gateway Title', 'wp-smartpay'),
            //     'desc'  => __('Set a custom title for the payment page. If you don\'t set, it will use the default value.', 'wp-smartpay'),
            //     'type'  => 'text',
            // ),
            // array(
            //     'id'    => 'paddle_checkout_icon',
            //     'name'  => __('Gateway Icon', 'wp-smartpay'),
            //     'desc'  => __('Gateway Icon URL must be including http:// or https://. If you don\'t set, it will use the default value.', 'wp-smartpay'),
            //     'type'  => 'upload',
            //     'size'  => 'regular',
            // ),
            // array(
            //     'id'    => 'paddle_checkout_image',
            //     'name'  => __('Checkout Image URL', 'wp-smartpay'),
            //     'desc'  => __('Checkout Image URL must be including https://. If you don\'t set, it will use the default value.', 'wp-smartpay'),
            //     'type'  => 'upload',
            //     'size'  => 'regular',
            // ),
            array(
                'id'    => 'paddle_checkout_location',
                'name'  => __('Checkout Location', 'wp-smartpay'),
                'desc'  => __('Select Checkout Location', 'wp-smartpay'),
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
                'desc'  => __('<p><b>Warning:</b> You must set the Instant Notification System (INS) for Paddle Checkout.<br>', 'wp-smartpay'),
                'type'  => 'descriptive_text',
            ),

            $paddle_webhook_description_text = __(
                sprintf(
                    '<p>For Paddle to function completely, you must configure your Instant Notification System. Visit your <a href="%s" target="_blank">account dashboard</a> to configure them. Please add the URL below to all notification types. It doesn\'t work for localhost or local IP.</p><p><b>INS URL:</b> <code>%s</code></p>.',
                    'https://vendors.paddle.com/alerts-webhooks',
                    home_url("index.php?smartpay-listener=paddle")
                ),
                'wp-smartpay'
            ),

            $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? $paddle_webhook_description_text .= __('<p><b>Warning!</b> It seems you are on the localhost.</p>', 'wp-smartpay') : '',

            array(
                'id'    => 'paddle_webhook_description',
                'type'  => 'descriptive_text',
                'name'  => __('Instant Notification System (INS)', 'wp-smartpay'),
                'desc'  => $paddle_webhook_description_text,

            ),
        );

        return array_merge($settings, ['paddle' => $gateway_settings]);
    }
}