<?php

namespace SmartPay\Modules\Shortcode;

use SmartPay\Models\Product;
use SmartPay\Models\Form;

class Shortcode
{
    public function __construct()
    {
        // Form shortcode
        add_shortcode('smartpay_form', [$this, 'form_shortcode']);

        // Product shortcode
        add_shortcode('smartpay_product', [$this, 'product_shortcode']);

        // Payment receipt shortcode
        add_shortcode('smartpay_payment_receipt', [$this, 'payment_receipt_shortcode']);

        // Customer dashboard shortcode
        add_shortcode('smartpay_dashboard', [$this, 'dashboard_shortcode']);
    }

    /**
     * Form shortcode.
     *
     * @since 0.0.1
     * @return void
     */
    public function form_shortcode($atts)
    {
        extract(shortcode_atts([
            'id' => null,
            'behavior'  => 'popup',
            'label'     => '',
        ], $atts));

        if (!isset($id)) return;

        // TODO: Add message if no payment method setup

        // TODO: need to remove
        // $form = smartpay_get_form($id);
        $form = Form::where('id', $id)->first();

        if (!isset($form)) return;

        // TODO: need to implement this function
        // if (!$form->can_pay()) {
        //     echo 'You can\'t pay on this form.';
        //     return;
        // }

        try {
            ob_start();

            echo smartpay_view('shortcodes.form', ['form' => $form, 'behavior' => $behavior, 'label' => $label]);

            return ob_get_clean();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Product shortcode.
     *
     * @since 0.0.1
     * @return void
     */
    public function product_shortcode($atts)
    {
        extract(shortcode_atts([
            'id' => null,
            'behavior'  => 'popup',
            'label'     => '',
        ], $atts));

        if (!isset($id)) return;

        $product = Product::where('id', $id)->first();

        if (!isset($product)) return;

        // if (!$product->can_purchase()) {
        //     echo 'You can\'t buy this product';
        //     return;
        // }

        try {
            ob_start();

            echo smartpay_view('shortcodes.product', ['product' => $product, 'behavior' => $behavior, 'label' => $label]);

            return ob_get_clean();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Payment receipt shortcode.
     *
     * @since 0.0.1
     * @return void
     */
    public function payment_receipt_shortcode($atts)
    {
        // $payment_id = intval($_GET['payment-id'] ?? smartpay_get_session_payment_id());

        // if (!$payment_id) {
        //     return;
        // }

        // // Sometimes payment gateway need more time to complete a payment
        // sleep(3);

        // $payment = smartpay_get_payment($payment_id);

        // if (!$payment->ID) {
        //     return;
        // }

        // try {

        //     ob_start();

        //     echo smartpay_view_render('shortcodes/payment_receipt', ['payment' => $payment]);

        //     return ob_get_clean();
        // } catch (\Exception $e) {
        //     return $e->getMessage();
        // }
    }

    /**
     * Customer dashboard shortcode.
     *
     * @since 0.0.2
     * @return void
     */
    public function dashboard_shortcode($atts)
    {
        // // If not logged in or id not found, then return
        // if (!is_user_logged_in() || get_current_user_id() <= 0) {
        //     echo '<p>You must log in to access the dashboard!</p>';
        //     return;
        // }

        // $customer = new SmartPay_Customer(get_current_user_id(), true);

        // ob_start();

        // echo smartpay_view_render('shortcodes/customer_dashboard', ['customer' => $customer]);

        // return ob_get_clean();
    }
}