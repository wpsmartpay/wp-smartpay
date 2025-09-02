<?php

namespace SmartPay\Modules\Gateway\Gateways\ManualPurchase;

use SmartPay\Foundation\PaymentGateway;
use SmartPay\Models\Payment;
use SmartPay\Models\Product;


defined('ABSPATH') || exit;

final class FreePurchase extends PaymentGateway
{
    public function __construct()
    {
        $this->init_actions();
    }

    /**
     * Main class Instance.
     *
     * Ensures that only one instance of class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  0.0.1
     * @return object
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof FreePurchase)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init_actions()
    {
        add_action('smartpay_free_ajax_process_payment', [$this, 'ajax_process_payment']);
        add_action('smartpay_free_subscription_process_payment', [$this, 'subscriptionProcessPayment'], 10, 2);

    }

    public function ajax_process_payment($payment_data)
    {
        global $smartpay_options;

        // check payment type is product purchase and should not provide for form payment
        if (Payment::PRODUCT_PURCHASE !== $payment_data['payment_type']) {
            die('Free Purchase only available for products only.');
        }

        $product = Product::where('id', $payment_data['payment_data']['product_id'])->first();
        if ($product) {
            if ($product->sale_price == $payment_data['amount']) {
                $payment = smartpay_insert_payment($payment_data);
                if ($payment) {
                    smartpay_debug_log(__(sprintf(
                        'SmartPay-FreePurchase: Payment #%s status changed to Pending.',
                        $payment->id
                    ), 'smartpay'));
                }

                if (!$payment->id) {
                    wp_redirect(get_permalink($smartpay_options['payment_failure_page']), 302);
                    smartpay_debug_log(__(sprintf(
                        'SmartPay-FreePurchase: Payment #%s Can\'t insert payment.',
                        $payment->id
                    ), 'smartpay'));
                    die('Can\'t insert payment.');
                }
                // Process the subscription
                if (Payment::BILLING_TYPE_SUBSCRIPTION === $payment_data['payment_data']['billing_type']) {
                    do_action('smartpay_free_subscription_process_payment', $payment, $payment_data);
                }
                if ($payment->updateStatus(Payment::COMPLETED)) {
                    $payment->setTransactionId('Manual-Payment');
                    smartpay_debug_log(__(sprintf(
                        'SmartPay-FreePurchase: Payment #%s status changed to Completed.',
                        $payment->id
                    ), 'smartpay'));
                }
                $return_url = add_query_arg('smartpay-payment', $payment->uuid, smartpay_get_payment_success_page_uri());
                echo 'Please be patient. Your payment is being processed';
                $content = '<script type="text/javascript">';
//                $content .= 'window.location.replace("'.$return_url.'");';
                $content .= 'window.location.href = "'.$return_url.'";';
                $content .= '</script>';

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo $content;
            } else {
                smartpay_debug_log(__('SmartPay-FreePurchase: Sale price could not matched', 'smartpay'));
                die('Are you cheating?.');
            }
        }
        die();
    }

    /**
     * process subscription
     *
     * @param [type] $payment
     * @param [type] $paymentData
     * @return void
     */
    public function subscriptionProcessPayment($payment, $paymentData)
    {
        // should not provide for recurring payment it has no means
        die('Recurring payment is not allowed for free purchase.');
    }
}
