<?php

namespace ThemesGrove\SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Payment
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Payment class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'process_payment']);
    }

    /**
     * Main Payment Instance.
     *
     * Ensures that only one instance of Payment exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Payment
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Shortcode)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function process_payment()
    {
        if ('smartpay_checkout' === $this->get_relative_url_path()) {

            if (!isset($_POST['smartpay_shortcode_nonce']) || !wp_verify_nonce($_POST['smartpay_shortcode_nonce'], 'smartpay_shortcode_nonce')) {
                wp_redirect(home_url('/'));
            }

            extract(sanitize_post($_POST));

            $payment_id = wp_insert_post(array(
                'post_type' => 'smartpay_payment',
                'post_status' => 'pending',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ));

            if ($payment_id) {
                add_post_meta($payment_id, '_first_name', $first_name);
                add_post_meta($payment_id, '_last_name', $last_name);
                add_post_meta($payment_id, '_email', $email);
                add_post_meta($payment_id, '_amount', $amount);
                add_post_meta($payment_id, '_form_id', $form_id);
            }

            die(var_dump($payment_id));
        }
    }

    private function get_relative_url_path()
    {
        return trim(add_query_arg(NULL, NULL), "/\\");
    }
}
