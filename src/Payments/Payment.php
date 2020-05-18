<?php

namespace SmartPay\Payments;

use stdClass;

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
        add_action('init', [$this, 'register_smartpay_payment_post_type']);

        add_action('init', [$this, 'process_payment']);

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

    public function register_smartpay_payment_post_type()
    {
        /** Payment Post Type */
        $payment_labels = array(
            'name'               => _x('Payments', 'post type general name', 'smartpay'),
            'singular_name'      => _x('Payment', 'post type singular name', 'smartpay'),
            'add_new'            => __('Add New', 'smartpay'),
            'add_new_item'       => __('Add New Payment', 'smartpay'),
            'edit_item'          => __('Edit Payment', 'smartpay'),
            'new_item'           => __('New Payment', 'smartpay'),
            'all_items'          => __('All Payments', 'smartpay'),
            'view_item'          => __('View Payment', 'smartpay'),
            'search_items'       => __('Search Payments', 'smartpay'),
            'not_found'          => __('No Payments found', 'smartpay'),
            'not_found_in_trash' => __('No Payments found in Trash', 'smartpay'),
            'parent_item_colon'  => '',
            'menu_name'          => __('Payment History', 'smartpay')
        );

        $payment_args = array(
            'labels'          => $payment_labels,
            'public'          => true,
            'show_in_menu'    => false,
            'query_var'       => false,
            'rewrite'         => false,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
            'supports'        => [],
            'can_export'      => true,
            'capabilities' => array(
                'create_posts' => false
            )
        );
        register_post_type('smartpay_payment', $payment_args);
    }

    public function process_payment()
    {
        if (isset($_POST['smartpay_action']) && 'smartpay_process_payment' === $_POST['smartpay_action']) {

            if (!isset($_POST['smartpay_process_payment']) || !wp_verify_nonce($_POST['smartpay_process_payment'], 'smartpay_process_payment')) {
                wp_redirect(home_url('/'));
            }
            extract(sanitize_post($_POST));

            // if (empty($smartpay_first_name) || empty($smartpay_last_name) || empty($smartpay_email) || empty($smartpay_amount) || empty($smartpay_form_id)) {
            //     wp_redirect(home_url('/'));
            // }

            $payment_data = apply_filters('smartpay_payment_data', array(
                'purchase_type'   => $smartpay_purchase_type,
                'purchase_data'   => $this->_get_product_purchase_data($_POST),
                'date'            => date('Y-m-d H:i:s', time()),
                'amount'          => $this->_get_purchase_amount($_POST),
                'currency'        => smartpay_get_currency() ?? 'USD',
                'gateway'         => $smartpay_gateway,
                'customer'        => $this->_get_purchase_customer($_POST),
                'email'           => $smartpay_email,
                'key'             => strtolower(md5($smartpay_email . date('Y-m-d H:i:s') . rand(1, 10))),
            ));

            // Set session payment data
            smartpay_set_session_payment_data($payment_data);

            // Send info to the gateway for payment processing
            $this->_send_to_gateway($smartpay_gateway, $payment_data);
        }
    }

    private function _send_to_gateway($gateway, $payment_data)
    {
        $payment_data['gateway_nonce'] = wp_create_nonce('smartpay-gateway');

        // $gateway must match the ID used when registering the gateway
        do_action('smartpay_' . $gateway . '_process_payment', $payment_data);
    }

    private function _get_product_purchase_data($_data)
    {
        extract(sanitize_post($_data));

        $purchase_data = [];

        if ('product_purchase' == $smartpay_purchase_type) {

            $product = smartpay_get_product($smartpay_product_id);

            // TODO: Implement variations
            $purchase_data = new stdClass();

            $purchase_data->type        = 'product_purchase';
            $purchase_data->product_id  = $product->get_ID();
            $purchase_data->amount      = $product->get_sale_price() ?? $product->get_base_price();
        } else if ('form_payment' == $smartpay_purchase_type) {

            // TODO: Implement form payment
        }

        return $purchase_data;
    }

    private function _get_purchase_amount($_data)
    {
        extract(sanitize_post($_data));

        $amount = 0;

        // TODO: Implement variations
        if ('product_purchase' == $smartpay_purchase_type) {

            $product = smartpay_get_product($smartpay_product_id);

            $amount = $product->get_sale_price() ?? $product->get_base_price();
        } else if ('form_payment' == $smartpay_purchase_type) {

            // TODO: Implement form payment
        }
        return $amount;
    }

    private function _get_purchase_customer($_data)
    {
        extract(sanitize_post($_data));

        $customer = new stdClass();

        $customer->first_name = $smartpay_first_name;
        $customer->last_name  = $smartpay_last_name;
        $customer->email      = $smartpay_email;

        return $customer;
    }

    public function insert_payment($payment_data)
    {
        if (empty($payment_data)) {
            return false;
        }

        $payment = new SmartPay_Payment();

        $payment->purchase_type  = $payment_data['purchase_type'];
        $payment->purchase_data  = $payment_data['purchase_data'];
        $payment->date           = $payment_data['date'];

        $payment->amount         = $payment_data['amount'];
        $payment->currency       = $payment_data['currency'] ?? smartpay_get_currency();
        $payment->gateway        = $payment_data['gateway'] ?? smartpay_get_default_gateway();

        $payment->customer       = $payment_data['customer'];
        $payment->email          = $payment_data['email'];

        $payment->key            = $payment_data['key'];
        $payment->mode           = smartpay_is_test_mode() ? 'test' : 'live';
        $payment->parent_payment = !empty($payment_data['parent']) ? absint($payment_data['parent']) : '';
        $payment->post_status    = $payment_data['status'] ?? 'pending';
        $payment->status         = $payment_data['status'] ?? 'pending';

        $payment->save();

        do_action('smartpay_after_insert_payment', $payment);


        if (!empty($payment->ID)) {
            // Set session payment id
            smartpay_set_session_payment_id($payment->ID);

            return $payment;
        }

        // Return false if no payment was inserted
        return false;
    }

    public function get_payment($payment_or_txn_id, $by_txn = false)
    {
        return new SmartPay_Payment($payment_or_txn_id, $by_txn);
    }

    public function enqueue_payment_scripts()
    {
        wp_register_script('smartpay-payment', plugins_url('/assets/js/payment.js', SMARTPAY_FILE), array('jquery'), SMARTPAY_VERSION);

        wp_enqueue_script('smartpay-payment');
    }
}
