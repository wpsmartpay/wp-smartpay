<?php

namespace SmartPay\Payments;

use SmartPay\Models\SmartPay_Payment;

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
        add_shortcode('smartpay_payment_receipt', [$this, 'smartpay_payment_receipt_shortcode']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_payment_scripts']);
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
        if (isset($_POST['smartpay_action']) && 'smartpay_process_payment' === $_POST['smartpay_action']) {

            if (!isset($_POST['smartpay_process_payment']) || !wp_verify_nonce($_POST['smartpay_process_payment'], 'smartpay_process_payment')) {
                wp_redirect(home_url('/'));
            }

            extract(sanitize_post($_POST));

            if (empty($first_name) || empty($last_name) || empty($email) || empty($amount) || empty($form_id)) {
                wp_redirect(home_url('/'));
            }

            $payment_data = array(
                'form_id'    => $form_id,
                'post_date'  => date('Y-m-d H:i:s', time()),
                'amount'     => $amount,
                'currency'   => smartpay_get_currency() ?? 'USD',
                'gateway'    => $gateway,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email,
                'key'        => strtolower(md5($email . date('Y-m-d H:i:s') . rand(1, 10))),
            );

            // Send info to the gateway for payment processing
            $this->_send_to_gateway($gateway, $payment_data);

            return;
        }
    }

    private function _send_to_gateway($gateway, $payment_data)
    {

        $payment_data['gateway_nonce'] = wp_create_nonce('smartpay-gateway');

        // $gateway must match the ID used when registering the gateway
        do_action('smartpay_' . $gateway . '_process_payment', $payment_data);

        die();
    }

    public static function insert_payment($payment_data)
    {
        if (empty($payment_data)) {
            return false;
        }

        $payment = new SmartPay_Payment();

        $payment->form_id           = $payment_data['form_id'];
        $payment->date           = $payment_data['post_date'];
        $payment->amount           = $payment_data['amount'];
        $payment->currency       = $payment_data['currency'] ?? smartpay_get_currency();
        $payment->payment_gateway        = $payment_data['gateway'] ?? smartpay_get_default_gateway();
        $payment->first_name     = $payment_data['first_name'];
        $payment->last_name      = $payment_data['last_name'];
        $payment->email          = $payment_data['email'];
        $payment->key            = $payment_data['key'];
        $payment->mode           = smartpay_is_test_mode() ? 'test' : 'live';
        $payment->parent_payment = !empty($payment_data['parent']) ? absint($payment_data['parent']) : '';
        $payment->status         = !empty($payment_data['status']) ? $payment_data['status'] : 'pending';
        $payment->save();

        do_action('smartpay_insert_payment', $payment->ID, $payment_data);

        if (!empty($payment->ID)) {
            // Add session
            $_SESSION['smartpay_payment_id'] = $payment->ID;

            return $payment->ID;
        }

        // Return false if no payment was inserted
        return false;
    }

    public static function get_payment($payment_or_txn_id, $by_txn = false){
        return new SmartPay_Payment($payment_or_txn_id, $by_txn);
    }

    public function smartpay_payment_receipt_shortcode($atts, $content = null)
    {
        ob_start();

        echo smartpay_view_render('payment/shortcode/receipt');

        return ob_get_clean();
    }

    public function enqueue_payment_scripts()
    {
        wp_register_script('smartpay-payment', plugins_url('/assets/js/payment.js', SMARTPAY_FILE), array('jquery'), SMARTPAY_VERSION);

        wp_enqueue_script('smartpay-payment');
    }
}