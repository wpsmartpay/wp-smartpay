<?php

namespace ThemesGrove\SmartPay;

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
        add_shortcode('smartpay_form', [$this, 'smartpay_form_shortcode']);
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

    public function smartpay_form_shortcode($atts)
    {
        extract(shortcode_atts([
            'id' => null,
        ], $atts));

        if (!isset($id)) {
            return;
        }

        $form = get_post($id);

        $data = [
            'form_id' => $form->ID,
            'amount' => get_post_meta($id, '_form_amount', true),
        ];

        return view_render('shortcode/payment_form', $data);
    }
}