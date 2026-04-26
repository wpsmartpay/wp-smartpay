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

        // For product purchases verify the product price is genuinely 0.
        if ( Payment::PRODUCT_PURCHASE === $payment_data['payment_type'] ) {
            $product = Product::where('id', $payment_data['payment_data']['product_id'])->first();
            if ( ! $product || floatval( $product->sale_price ) != 0 ) {
                smartpay_debug_log( __( 'SmartPay-FreePurchase: product not found or price is not zero.', 'smartpay' ) );
                die( 'Free gateway is only allowed for zero-price products.' );
            }
        }

        // For any payment type: amount must be 0.
        if ( floatval( $payment_data['amount'] ) != 0 ) {
            smartpay_debug_log( __( 'SmartPay-FreePurchase: amount is not zero.', 'smartpay' ) );
            die( 'Free gateway is only allowed for zero-amount orders.' );
        }

        $payment = smartpay_insert_payment($payment_data);

        if ( ! $payment || ! $payment->id ) {
            wp_redirect( get_permalink( $smartpay_options['payment_failure_page'] ), 302 );
            smartpay_debug_log(
                sprintf(
                    /* translators: 1: Payment id */
                    __( 'SmartPay-FreePurchase: Payment #%s Can\'t insert payment.', 'smartpay' ),
                    $payment->id ?? 0
                )
            );
            die( "Can't insert payment." );
        }

        smartpay_debug_log(
            sprintf(
                /* translators: 1: Payment id */
                __( 'SmartPay-FreePurchase: Payment #%s status changed to Pending.', 'smartpay' ),
                $payment->id
            )
        );

        // Allow subscription hook to run before marking completed.
        $billing_type = $payment_data['payment_data']['billing_type'] ?? '';
        if ( Payment::BILLING_TYPE_SUBSCRIPTION === $billing_type ) {
            do_action( 'smartpay_free_subscription_process_payment', $payment, $payment_data );
        }

        if ( $payment->updateStatus( Payment::COMPLETED ) ) {
            $payment->setTransactionId( 'Free-Purchase' );
            smartpay_debug_log(
                sprintf(
                    /* translators: 1: Payment id */
                    __( 'SmartPay-FreePurchase: Payment #%s status changed to Completed.', 'smartpay' ),
                    $payment->id
                )
            );
        }

        $return_url = add_query_arg( 'smartpay-payment', $payment->uuid, smartpay_get_payment_success_page_uri() );

        wp_send_json_success( array( 'redirect' => $return_url ) );
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
