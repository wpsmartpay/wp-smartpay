<?php

namespace SmartPay\Modules\Gateway\Gateways;

use SmartPay\Foundation\PaymentGateway;
use SmartPay\Models\Payment;

class PaypalStandard extends PaymentGateway
{
    /** @var array Supported currency */
    private static $supported_currency = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'];

    /**
     * Construct Paypal class.
     *
     * @since  0.0.5
     * @access public
     */
    public function __construct()
    {
        if (!smartpay_is_gateway_active('paypal')) {
            return;
        }

        $this->_checkApiKeys();

        if (!in_array(strtoupper(smartpay_get_currency()), self::$supported_currency)) {
            add_action('admin_notices', [$this, 'unsupported_currency_notice']);
            return;
        }

        // Initialize actions.
        $this->initActions();
    }

    //check api keys set or not
    private function _checkApiKeys()
    {
        $paypal_email = smartpay_get_option('paypal_email') ?? null;

        if (empty($paypal_email)) {
            add_action('admin_notices', function () {
				echo sprintf('<div class="error"><p><strong>'.
				     esc_html__('Paypal credentials was not set yet!', 'smartpay').'</strong> '. esc_html__('To get the Paypal service on smartpay, you must add your paypal business email.', 'smartpay'). ' <a href="%s"> '. esc_html__(' Input your paypal credentials', 'smartpay'). '</a></p></div>',
					esc_url(admin_url('admin.php?page=smartpay-setting&tab=gateways&section=paypal'))
				);
            });
        }
    }

    /**
     * Initialize wp actions.
     *
     * @access private
     * @since  0.0.5
     * @return void
     */
    private function initActions()
    {
        add_action('smartpay_paypal_process_payment', [$this, 'processPayment']);

        add_action('smartpay_before_payment_receipt', [$this, 'addWarningMessage']);

        add_action('smartpay_paypal_ajax_process_payment', [$this, 'ajaxProcessPayment']);

        add_filter('smartpay_settings_sections_gateways', [$this, 'gatewaySection']);

        add_filter('smartpay_settings_gateways', [$this, 'gatewaySettings']);

        add_action('init', [$this, 'processWebhooks']);

        add_action('smartpay_paypal_web_accept', [$this, 'process_smartpay_paypal_web_accept'], 10, 2);
    }

	public function addWarningMessage( Payment $payment ): void {
		// phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
		if ( isset( $_GET['PayerID'] ) && strtolower($payment->status) === Payment::PENDING && $payment->gateway === 'paypal' ) {
			$message = __( 'Thank you for your payment.Your payment is processing and will be completed within few seconds. <strong>Do not pay again</strong>.',
				'smartpay' );
			echo '<div class="smartpay">';
			echo '<div class="receipt-alert receipt-alert-success">';
			echo '<p>' . wp_kses_post($message) . '</p>';
			echo '<a class="receipt-alert-close">&times;</a>';
			echo '</div>';
			echo '</div>';
		}
	}

    public function ajaxProcessPayment($paymentData)
    {
        global $smartpay_options;

        if (!$this->_check_credentials()) {
            // TODO: Implement smartpay_set_error
            die('Credentials error.');
        }

        $payment = smartpay_insert_payment($paymentData);

        if (!$payment->id) {
            die('Can\'t insert payment.');
        }

        $payment_price = number_format($paymentData['amount'], 2, '.', '');

        $default_args = [
            'charset'       => get_bloginfo('charset'),
            'lc'            => get_locale(),
            'cbt'           => get_bloginfo('name'),
            'page_style'    => 'paypal',
            'bn'            => 'WPSmartPay',
            'image_url'     => '',

            'business'      => $smartpay_options['paypal_email'],

            'cmd'           => '_cart',
            'email'         => $paymentData['email'],
            'first_name'    => $paymentData['customer']['first_name'],
            'last_name'     => $paymentData['customer']['last_name'],
            'currency_code' => $paymentData['currency'],

            'custom'        => $payment->id,
            'invoice'       => $paymentData['key'],

            'rm'            => 2,
            'no_shipping'   => 1,
            'no_note'       => 1,
            'tax_rate'      => 0,
            'upload'        => 1,

            'return'        => add_query_arg('smartpay-payment', $payment->uuid, smartpay_get_payment_success_page_uri()),
            'cancel_return' => add_query_arg(['payment-id' => $payment->id], smartpay_get_payment_failure_page_uri()),
            'notify_url'    => add_query_arg(['smartpay-listener' => 'paypal', 'payment-id' => $payment->id], get_bloginfo('url') . '/index.php'),
        ];

        if (Payment::BILLING_TYPE_SUBSCRIPTION === $paymentData['billing_type']) {
            do_action('smartpay_paypal_subscription_process_payment', $payment, $paymentData);
            $default_args['item_name']    = 'Payment #' . $payment->id;
            $default_args['a3']           = $payment_price;
            $default_args['p3']           = smartpay_get_paypal_time_duration_option($paymentData['billing_period']);
            $default_args['t3']           = smartpay_get_paypal_time_option($paymentData['billing_period']);
            $default_args['src']          = 1;
            $default_args['cmd']          = '_xclick-subscriptions';
        } else {
            // TODO: Rearrange data
            $default_args['item_name_1']    = 'Payment #' . $payment->id;
            $default_args['item_number_1']  = $payment->id;
            $default_args['amount_1']    = $payment_price;
        }

        $paypal_args = apply_filters('smartpay_paypal_redirect_args', $default_args, $paymentData);

        $paypal_redirect = trailingslashit($this->get_paypal_redirect_url()) . '?' . http_build_query($paypal_args);

        $content = '<p class="text-center">Redirecting to PayPal...</p>';
        $content .= '<script>window.location.replace("' . $paypal_redirect . '");</script>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
        echo $content;
        return;
    }

    /**
     * Process webhook requests.
     *
     * @since  0.0.5
     * @return void
     * @access public
     */
    public function processWebhooks()
    {
        global $smartpay_options;
	    // phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
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
	        // phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce need
            $payment_id = absint($encoded_data_array['custom'] ?? sanitize_text_field($_GET['payment-id']) ?? 0);

            $payment = smartpay_get_payment($payment_id);

            // If payment not found
            if (!$payment) {
                echo sprintf(
					/* translators: 1: payment id */
					esc_html__( 'SmartPay-Paypal: Webhook requested; Smartpay payment not found for #%s.','smartpay' ), esc_html($payment_id));
                die('Error.');
            }

            if (has_action('smartpay_paypal_' . $encoded_data_array['txn_type'])) {
                // Allow PayPal IPN types to be processed separately
                do_action('smartpay_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id);
            } else {
                // Fallback to web accept just in case the txn_type isn't present
                do_action('smartpay_paypal_web_accept', $encoded_data_array, $payment_id);
            }
            return;
        }
    }

    /**
     * Process web accept (one time) payment IPNs
     *
     * @since 0.0.5
     * @param array $data IPN Data
     * @return void
     */
    public function process_smartpay_paypal_web_accept($data, $payment_id)
    {
        if ($data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded') {
            return;
        }

        // Collect payment details
        $paypal_amount  = $data['mc_gross'] ?? 0;
        $payment_status = strtolower($data['payment_status'] ?? '');
        $payment = Payment::find($payment_id);

        if (!$payment) {
            smartpay_debug_log(sprintf(
				/* translators: 1: payment id */
				__( 'SmartPay-PayPal: Payment #%s no found.', 'smartpay' ), $payment_id)
            );
        }

        if ($payment_status == 'refunded' || $payment_status == 'reversed') {
            // TODO: Process a refund
        } else {

            if ('publish' == $payment->status) {
                return; // Only complete payments once
            }

            if (number_format((float) $paypal_amount, 2) < number_format((float) $payment->amount, 2)) {
                return; // The prices don't match
            }

            if ('Completed' == $payment_status || smartpay_is_test_mode()) {
                $payment->updateStatus('completed');
                $payment->setTransactionId($data['txn_id']);

                smartpay_debug_log(sprintf(
					/* translators: 1: payment id */
					__( 'SmartPay-PayPal: Payment #%s completed.', 'smartpay' ), $payment->id
	                ) );
            }
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
    public function gatewaySection(array $sections = array()): array
    {
        $sections['paypal'] = __('PayPal Standard', 'smartpay');

        return $sections;
    }

    /**
     * Register the gateway settings for Paypal
     *
     * @since  0.0.5
     * @param array $settings
     * @return array
     * @access public
     */
    public function gatewaySettings(array $settings): array
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
                'desc'  => sprintf(
					__('Enter your PayPal Identity Token in order to enable Payment Data Transfer (PDT). This allows payments to be verified without relying on the PayPal IPN. See our', 'smartpay') . '<a href="%s" target="_blank">'. __('documentation', 'smartpay') .'</a>' . __(' for further information.', 'smartpay'),
	                esc_url('https://developer.paypal.com/docs/api-basics/notifications/payment-data-transfer/#get-started')
                ),
                'type'  => 'text'
            ),
            array(
                'id'    => 'disable_paypal_verification',
                'name'  => __('Disable PayPal IPN Verification', 'smartpay'),
                'desc'  => sprintf(__('If you are unable to use Payment Data Transfer and payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases. See our', 'smartpay').' <a href="%s" target="_blank">'. __('FAQ', 'smartpay') .'</a>'. __('for further information.', 'smartpay'), '#'),
                'type'  => 'checkbox',
            ),

            array(
                'id'    => 'paypal_smartpay_doc_link',
                'name'  => __('WPSmartPay Documentation Link', 'smartpay'),
                'desc'  => sprintf(__('Please see our', 'smartpay').' <a href="%s" target="_blank">'.__('documentation', 'smartpay').'</a>'. __('to set up PayPal properly.', 'smartpay'),
                 esc_url('https://wpsmartpay.com/docs/wpsmartpay/configure-payment-methods/how-to-setup-paypal')
                ),
                'type'  => 'descriptive_text'
            ),
        );

        return array_merge($settings, ['paypal' => $gateway_settings]);
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
        global $smartpay_options;

        $paypal_email       = $smartpay_options['paypal_email'] ?? null;

        if (empty($paypal_email)) {
            // TODO: Add smartpay payment error notice

            die('SmartPay-PayPal: Set credentials; You must enter your business email for PayPal in gateway settings.');
        }

        return true;
    }

    public function unsupported_currency_notice()
    {
	    echo sprintf(
		    '<div class="error"><p>'.
		    esc_html__('Unsupported currency! Your currency ', 'smartpay') .
		    '<code>%1$s</code>'.
		    esc_html__(' does not supported by PayPal. Please change your currency from ', 'smartpay') .
		    '<a href="%2$s">'.
		    esc_html__('currency setting', 'smartpay').
		    '</a>.</p></div>',
		    esc_html(strtoupper(smartpay_get_currency())),
		    esc_url(admin_url('admin.php?page=smartpay-setting&tab=general'))
	    );
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
