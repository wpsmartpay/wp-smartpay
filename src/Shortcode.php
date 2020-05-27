<?php

namespace SmartPay;

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
     * @since 0.1
     */
    private function __construct()
    {
        add_shortcode('smartpay_form', [$this, 'form_shortcode']);

        add_shortcode('smartpay_product', [$this, 'product_shortcode']);

        add_shortcode('smartpay_payment_receipt', [$this, 'payment_receipt_shortcode']);

        add_shortcode('smartpay_payment_history', [$this, 'payment_history_shortcode']);
    }

    /**
     * Main Shortcode Instance.
     *
     * Ensures that only one instance of Shortcode exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
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

    public function form_shortcode($atts)
    {
        // global $smartpay_options;

        extract(shortcode_atts([
            'id' => null,
        ], $atts));

        if (!isset($id)) {
            return;
        }

        // TODO: Add message if no payment method setup

        $form = get_post($id);

        if ($form && 'publish' === $form->post_status) {

            $data = [
                'form_id'                           => $form->ID,
                'amount'                            => get_post_meta($form->ID, '_form_amount', true),
                'payment_type'                      => get_post_meta($form->ID, '_form_payment_type', true),
                'payment_button_text'               => get_post_meta($form->ID, '_form_payment_button_text', true),
                'payment_button_processing_text'    => get_post_meta($form->ID, '_form_payment_button_processing_text', true),
                'payment_button_style'              => get_post_meta($form->ID, '_form_payment_button_style', true),
                'paddle_checkout_image'             => get_post_meta($form->ID, '_form_paddle_checkout_image', true),
                'paddle_checkout_location'          => get_post_meta($form->ID, '_form_paddle_checkout_location', true),
                'form_image'          => has_post_thumbnail($form->ID) ? wp_get_attachment_url(get_post_thumbnail_id($form->ID), 'thumbnail') : ''
            ];

            try {
                ob_start();

                echo smartpay_view_render('shortcodes/form', $data);

                return ob_get_clean();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function product_shortcode($atts)
    {
        extract(shortcode_atts([
            'id' => null,
        ], $atts));

        if (!isset($id)) {
            return;
        }
        // TODO: Add message if no payment method setup

        $product = smartpay_get_product($id);

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

    public function payment_history_shortcode($atts)
    {
        ob_start();

        echo smartpay_view_render('shortcodes/payment_history');

        return ob_get_clean();
    }
}