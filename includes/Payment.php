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
        if ('smartpay-payment' === $this->get_relative_url_path()) {

            if (!isset($_POST['smartpay_process_payment']) || !wp_verify_nonce($_POST['smartpay_process_payment'], 'smartpay_process_payment')) {
                wp_redirect(home_url('/'));
            }

            extract(sanitize_post($_POST));

            if (empty($first_name) || empty($last_name) || empty($email) || empty($amount) || empty($form_id)) {
                wp_redirect(home_url('/'));
            }

            $payment_data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'amount' => $amount,
                'gateway' => $gateway,
                'form_id' => $form_id,
            );

            // Send info to the gateway for payment processing
            smartpay_send_to_gateway($gateway, $payment_data);

            return;
        }
    }

    public static function insert($payment_data)
    {

        if (!isset($payment_data['gateway_nonce']) || !wp_verify_nonce($payment_data['gateway_nonce'], 'smartpay-gateway')) {
            return false;
        }

        if (empty($payment_data['first_name']) || empty($payment_data['last_name']) || empty($payment_data['email']) || empty($payment_data['amount'])  || empty($payment_data['gateway']) || empty($payment_data['form_id'])) {
            return false;
        }
        $payment_id = wp_insert_post(array(
            'post_type' => 'smartpay_payment',
            'post_status' => 'pending',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ));

        if ($payment_id) {
            add_post_meta($payment_id, '_first_name', $payment_data['first_name']);
            add_post_meta($payment_id, '_last_name', $payment_data['last_name']);
            add_post_meta($payment_id, '_email', $payment_data['email']);
            add_post_meta($payment_id, '_amount', $payment_data['amount']);
            add_post_meta($payment_id, '_gateway', $payment_data['gateway']);
            add_post_meta($payment_id, '_form_id', $payment_data['form_id']);

            // Add session
            $_SESSION['smartpay_payment_id'] = $payment_id;
        }

        return $payment_id;
    }

    private function get_relative_url_path()
    {
        return trim(add_query_arg(NULL, NULL), "/\\");
    }
}