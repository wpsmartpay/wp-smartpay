<?php

namespace SmartPay;

use SmartPay\Customers\SmartPay_Customer;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Shortcode
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Shortcode class.
     *
     * @since 0.0.1
     */
    private function __construct()
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
     * Main Shortcode Instance.
     *
     * Ensures that only one instance of Shortcode exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     *
     * @return object|Shortcode
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Shortcode)) {
            self::$instance = new self();
        }
        return self::$instance;
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
        ], $atts));

        if (!isset($id)) return;

        // TODO: Add message if no payment method setup

        $form = smartpay_get_form($id);

        if (!isset($form)) return;

        if (!$form->can_pay()) {
            echo 'You can\'t pay on this form.';
            return;
        }

        try {
            ob_start();

            echo smartpay_view_render('shortcodes/form', ['form' => $form]);

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
        ], $atts));

        if (!isset($id)) return;

        // TODO: Add message if no payment method setup

        $product = smartpay_get_product($id);

        if (!isset($product)) return;

        if (!$product->can_purchase()) {
            echo 'You can\'t buy this product';
            return;
        }

        try {
            ob_start();

            echo smartpay_view_render('shortcodes/product', ['product' => $product]);

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
        $payment_id = smartpay_get_session_payment_id();

        if (!isset($payment_id)) {
            return;
        }

        $payment = smartpay_get_payment($payment_id);

        try {
            ob_start();

            echo smartpay_view_render('shortcodes/payment_receipt', ['payment' => $payment]);

            return ob_get_clean();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Customer dashboard shortcode.
     *
     * @since 0.0.2
     * @return void
     */
    public function dashboard_shortcode($atts)
    {
        // If not logged in or id not found, then return
        if (!is_user_logged_in() || get_current_user_id() <= 0) return;

        $customer = new SmartPay_Customer(get_current_user_id(), true);

        ob_start();

        echo smartpay_view_render('shortcodes/customer_dashboard', ['customer' => $customer]);

        return ob_get_clean();
    }
}