<?php

namespace SmartPay\Payments;

use SmartPay\Customers\SmartPay_Customer;
use SmartPay\Products\Product_Variation;

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
     * @since  0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'register_smartpay_payment_post_type']);

        add_action('init', [$this, 'process_payment']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_payment_scripts']);

        add_action('wp_ajax_smartpay_process_payment', [$this, 'ajax_process_payment']);

        add_action('wp_ajax_nopriv_smartpay_process_payment', [$this, 'ajax_process_payment']);

        add_action('smartpay_update_payment_status', [$this, 'complete_payment'], 100, 3);
    }

    /**
     * Main Payment Instance.
     *
     * Ensures that only one instance of Payment exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  0.0.1
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
        // TODO: Need to refactor
        if (isset($_POST['smartpay_action']) && 'smartpay_process_payment' === sanitize_text_field($_POST['smartpay_action'])) {

            if (!isset($_POST['smartpay_process_payment']) || !wp_verify_nonce($_POST['smartpay_process_payment'], 'smartpay_process_payment')) {
                wp_redirect(home_url('/'));
            }
            extract(sanitize_post($_POST));

            // if (empty($smartpay_first_name) || empty($smartpay_last_name) || empty($smartpay_email) || empty($smartpay_amount) || empty($smartpay_form_id)) {
            //     wp_redirect(home_url('/'));
            // }

            $payment_data = apply_filters('smartpay_payment_data', array(
                'payment_type'   => $smartpay_payment_type,
                'payment_data'   => $this->_get_payment_data(sanitize_post($_POST)),
                'date'            => date('Y-m-d H:i:s', time()),
                'amount'          => $this->_get_payment_amount(sanitize_post($_POST)),
                'currency'        => smartpay_get_currency() ?? 'USD',
                'gateway'         => $smartpay_gateway,
                'customer'        => $this->_get_payment_customer(sanitize_post($_POST)),
                'email'           => $smartpay_email,
                'key'             => strtolower(md5($smartpay_email . date('Y-m-d H:i:s') . rand(1, 10))),
            ));

            // Set session payment data
            smartpay_set_session_payment_data($payment_data);

            // Send info to the gateway for payment processing
            $this->_send_to_gateway($smartpay_gateway, $payment_data, false);
        }
    }

    function ajax_process_payment()
    {
        // TODO: Convert response to JSON

        if (!isset($_POST['data']['smartpay_action']) || 'smartpay_process_payment' != sanitize_text_field($_POST['data']['smartpay_action'])) {
            echo '<p class="text-danger">Payment process action not acceptable!</p>';
            die();
        }

        if (!isset($_POST['data']['smartpay_process_payment']) || !wp_verify_nonce($_POST['data']['smartpay_process_payment'], 'smartpay_process_payment')) {
            echo '<p class="text-danger">Payment process nonce verification failed!</p>';
            die();
        }

        $validate = $this->_checkValidation(sanitize_post($_POST['data']) ?? []);

        if (!$validate) {
            echo '<p class="text-danger">Payment data invalid!</p>';
            die();
        }

        $payment_data = $this->_prepare_payment_data(sanitize_post($_POST['data']) ?? []);

        if (!$payment_data || !is_array($payment_data)) {
            echo '<p class="text-danger">Payment data invalid!</p>';
            die();
        }

        // Set session payment data
        smartpay_set_session_payment_data($payment_data);

        // Send payment data toprocess gateway payment
        $this->_process_gateway_payment($payment_data);

        die();
    }

    private function _prepare_payment_data($_data)
    {
        $payment_data = $this->_get_payment_data($_data);

        return apply_filters('smartpay_prepare_payment_data', array(
            'payment_type'  => $_data['smartpay_payment_type'],
            'payment_data'  => $payment_data,
            'date'          => date('Y-m-d H:i:s', time()),
            'amount'        => $payment_data['total_amount'] ?? '',
            'currency'      => smartpay_get_currency() ?? 'USD',
            'gateway'       => $_data['smartpay_gateway'],
            'customer'      => $this->_get_payment_customer($_data),
            'email'         => $_data['smartpay_email'],
            'key'           => strtolower(md5($_data['smartpay_email'] . date('Y-m-d H:i:s') . rand(1, 10))),
        ));
    }

    private function _get_payment_data($_data)
    {
        $payment_type = $_data['smartpay_payment_type'] ?? '';

        switch ($payment_type) {

            case 'product_payment':

                $product_id = $_data['smartpay_product_id'] ?? '';

                $variation_id = $_data['smartpay_product_variation_id'] ?? '';

                $product = smartpay_get_product($product_id);

                if (empty($product_id) || empty($product)) return [];

                $product_price = $product->sale_price ?? $product->base_price;

                if ($product->has_variations() && !empty($variation_id)) {

                    $variation = new Product_Variation($variation_id);

                    return array(
                        'product_id'        => $product_id,
                        'variation_id'      => $variation_id,
                        'variation_name'    => $variation->name,
                        'product_price'     => $product_price,
                        'additional_amount' => $variation->additional_amount,
                        'total_amount'      => $product_price + $variation->additional_amount,
                    );
                } else {

                    return array(
                        'product_id'    => $product->ID,
                        'product_price' => $product_price,
                        'total_amount'  => $product_price,
                    );
                }
                break;

            case 'form_payment':

                $form_id = $_data['smartpay_form_id'] ?? '';

                $form = smartpay_get_form($form_id);

                if (empty($form_id) || empty($form)) return [];

                return [
                    'form_id' => $form->ID,
                    'total_amount' => $_data['smartpay_form_amount'] ?? 0,
                ];
                break;

            default:
                return [];
                break;
        }
    }

    private function _get_payment_amount($_data)
    {
        if ('product_payment' == $_data['smartpay_payment_type'] ?? '') {

            $product = smartpay_get_product($_data['smartpay_product_id'] ?? '');

            if (!$product) return;

            $product_price = $product->get_sale_price() ?? $product->get_base_price();

            if (count($product->variations) && isset($_data['smartpay_product_variation_id'])) {
                $variation = new Product_Variation($_data['smartpay_product_variation_id']);
                return $product_price + $variation->additional_amount ?? 0;
            }
        } else if ('form_payment' == $_data['smartpay_payment_type'] ?? '') {

            return $_data['smartpay_amount'] ?? 0;
        }

        return 0;
    }

    private function _get_payment_customer($_data)
    {
        $customer = new SmartPay_Customer($_data['smartpay_email']);

        if ($customer->ID) {
            $customer_id = $customer->ID;
        } else {
            $customer->user_id      = is_user_logged_in() ? get_current_user_id() : 0;
            $customer->first_name   = $_data['smartpay_first_name'];
            $customer->last_name    = $_data['smartpay_last_name'];
            $customer->email        = $_data['smartpay_email'];

            $customer_id = $customer->insert();
        }

        return [
            'customer_id' => $customer_id ?? 0,
            'first_name'  => $_data['smartpay_first_name'] ?? '',
            'last_name'   => $_data['smartpay_last_name'] ?? '',
            'email'       => $_data['smartpay_email'] ?? '',
        ];
    }

    private function _checkValidation($_data)
    {
        // TODO: Reform validation
        $email = $_data['smartpay_email'] ?? '';
        if (!is_email($email)) return false;

        return true;
    }

    public function insert_payment($payment_data)
    {
        if (empty($payment_data)) return;

        $payment = new SmartPay_Payment();

        $payment->payment_type   = $payment_data['payment_type'];
        $payment->payment_data   = $payment_data['payment_data'];
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

            // Attach payment to customer
            $this->attach_customer_payment($payment);

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

        wp_register_script('smartpay-payment', plugins_url('/assets/js/payment.js', SMARTPAY_FILE), array('jquery'), SMARTPAY_VERSION, true);

        wp_enqueue_script('smartpay-payment');

        wp_localize_script(
            'smartpay-payment',
            'smartpay',
            array('ajax_url' => admin_url('admin-ajax.php'))
        );
    }

    private function _process_gateway_payment($payment_data, $ajax = true)
    {
        $gateway = sanitize_text_field($_POST['data']['smartpay_gateway']) ?? '';

        if (!is_string($gateway) || !smartpay_is_gateway_active($gateway)) {
            echo '<p class="text-danger">Gateway is not active or not exist!</p>';
            return;
        }

        $payment_data['gateway_nonce'] = wp_create_nonce('smartpay-gateway');

        // gateway must match the ID used when registering the gateway
        if ($ajax) {
            do_action('smartpay_' . $gateway . '_ajax_process_payment', $payment_data);
        } else {
            do_action('smartpay_' . $gateway . '_process_payment', $payment_data);
        }
    }

    public function attach_customer_payment($payment)
    {
        $customer = new SmartPay_Customer($payment->email);

        $customer->attach_payment($payment->ID);
    }

    /**
     * Complete a payment
     *
     * Performs all necessary actions to complete a payment.
     *
     * @since x.x.x
     * @return void
     */
    public function complete_payment($payment, $new_status, $old_status)
    {
        // Make sure that payments are only completed once
        if ($old_status == 'publish' || $old_status == 'complete') return;

        // Make sure the payment completion is only processed when new status is complete
        if ($new_status != 'publish' && $new_status != 'complete') return;

        // Ensure this action only runs once ever
        if ($payment->completed_date) return;

        $payment->completed_date = current_time('mysql');
        $payment->save();

        /** Runs when a payment is marked as "complete" **/
        do_action('smartpay_complete_payment', $payment->ID);

        // TODO: Increase sales, amount and others
    }
}