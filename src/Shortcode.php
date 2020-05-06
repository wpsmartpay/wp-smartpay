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
        // global $smartpay_options;

        extract(shortcode_atts([
            'id' => null,
        ], $atts));

        if (!isset($id)) {
            return;
        }

        $form = get_post($id);

        if ($form && 'publish' === $form->post_status) {

            $has_keys = true;

            // Show a notice to admins if they have not setup paddle.
            if (!$has_keys && current_user_can('manage_options')) {
                return wp_kses_post(sprintf(
                    /* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
                    __('Please complete your %1$sPaddle Setup%2$s to view the payment form.', 'smartpay'),
                    sprintf(
                        '<a href="%s">',
                        add_query_arg(
                            array(
                                'page' => 'smartpay-setting',
                                'tab'  => 'gateways',
                            ),
                            admin_url('admin.php')
                        )
                    ),
                    '</a>'
                ));
                // Show nothing to guests if Stripe is not setup.
            } else if (!$has_keys && !current_user_can('manage_options')) {
                return '';
            }

            try {
                ob_start();

                self::render_html($form);

                return ob_get_clean();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public static function render_html($form)
    {

        $payment_page = smartpay_get_option('payment_page', 0);
        if ($payment_page) {
            $data = [
                'form_id'                           => $form->ID,
                'form_action'                       => get_permalink(absint($payment_page)),
                'amount'                            => get_post_meta($form->ID, '_form_amount', true),
                'payment_type'                      => get_post_meta($form->ID, '_form_payment_type', true),
                'amount'                            => get_post_meta($form->ID, '_form_amount', true),
                'payment_button_text'               => get_post_meta($form->ID, '_form_payment_button_text', true),
                'payment_button_processing_text'    => get_post_meta($form->ID, '_form_payment_button_processing_text', true),
                'payment_button_style'              => get_post_meta($form->ID, '_form_payment_button_style', true),
                'paddle_checkout_image'             => get_post_meta($form->ID, '_form_paddle_checkout_image', true),
                'paddle_checkout_location'          => get_post_meta($form->ID, '_form_paddle_checkout_location', true),
            ];

            echo smartpay_view_render('payment/shortcode/pay_now', $data);
        } else {
            echo 'Please setup your payment page.';
        }
    }
}
