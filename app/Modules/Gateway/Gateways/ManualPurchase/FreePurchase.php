<?php

namespace SmartPay\Modules\Gateway\Gateways\ManualPurchase;

use Razorpay\Api\Api;
use SmartPay\Foundation\PaymentGateway;
use SmartPay\Models\Payment;
use SmartPay\Models\Form;
use SmartPay\Models\Product;
use SmartPayPro\Models\Subscription;


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
        add_action('smartpay_razorpay_process_payment', [$this, 'process_payment']);
        add_action('smartpay_free_ajax_process_payment', [$this, 'ajax_process_payment']);
        add_action('smartpay_free_subscription_process_payment', [$this, 'subscriptionProcessPayment'], 10, 2);

    }

    public function process_payment($payment_data)
    {
        return;
    }

    public function ajax_process_payment($payment_data)
    {
        global $smartpay_options;

        $product = Product::where('id', $payment_data['payment_data']['product_id'])->first();
        if ($product) {
            if ($product->sale_price == $payment_data['amount']) {
                $payment = smartpay_insert_payment($payment_data);

                if (!$payment->id) {
                    wp_redirect(get_permalink($smartpay_options['payment_failure_page']), 302);
                    die('Can\'t insert payment.');
                }
                // Process the subscription
                if (Payment::BILLING_TYPE_SUBSCRIPTION === $payment_data['payment_data']['billing_type']) {
                    do_action('smartpay_free_subscription_process_payment', $payment, $payment_data);
                }
                if ($payment->updateStatus(Payment::COMPLETED)) {
                    $payment->setTransactionId('Manual-Payment');
                }
                $return_url = add_query_arg('payment-id', $payment->id, smartpay_get_payment_success_page_uri());
                echo 'Please be patient. Your payment is being processed';
                $content = '<script type="text/javascript">';
//                $content .= 'window.location.replace("'.$return_url.'");';
                $content .= 'window.location.href = "'.$return_url.'";';
                $content .= '</script>';
                echo $content;
            } else {
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

        if ($paymentData['billing_period'] === Subscription::BILLING_PERIOD_MONTHLY) {
            $interval = 1;
        }

        if ($paymentData['billing_period'] === Subscription::BILLING_PERIOD_QUARTERLY) {
            $interval = 3;
        }

        if ($paymentData['billing_period'] === Subscription::BILLING_PERIOD_SEMIANNUAL) {
            $interval = 6;
        }

        if ($paymentData['billing_period'] === Subscription::BILLING_PERIOD_YEARLY) {
            $interval = 12;
        }

        $productAmount  = $paymentData['amount'] * 100;

        // store the subscription data into subscription table
        $subscription = new Subscription();
        $subscription->period               = $paymentData['billing_period'];
        $subscription->recurring_amount     = $paymentData['amount'];
        $subscription->parent_payment_id    = $payment->id;
        $subscription->status               = Subscription::STATUS_PENDING;
        $subscription->save();
    }

}