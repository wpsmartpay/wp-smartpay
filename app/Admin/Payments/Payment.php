<?php

namespace SmartPay\Admin\Payments;

use SmartPay\Payments\SmartPay_Payment;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Payment
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Payment class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_menu', [$this, 'add_payment_details_page'], 10);

        add_filter('manage_smartpay_payment_posts_columns', [$this, 'smartpay_payment_columns']);

        add_filter('manage_smartpay_payment_posts_custom_column', [$this, 'smartpay_payment_column_data'], 10, 2);

        add_filter('post_row_actions', [$this, 'modify_smartpay_payment_admin_table'], 10, 2);

        add_action('init', [$this, 'update_payment_status']);
    }

    /**
     * Main Payment Instance.
     *
     * Ensures that only one instance of Payment exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
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

    public function add_payment_details_page()
    {
        add_submenu_page(
            '',
            'SmartPay - Payment Details',
            'Payment Details',
            'manage_options',
            'payment-details',
            function () {
                return smartpay_view('admin/payments/details');
            }
        );
    }

    public function smartpay_payment_columns($columns)
    {
        return [
            'cb'      => $columns['cb'],
            'id'      => __('Payment ID'),
            'name'    => __('Name'),
            'email'   => __('Email'),
            'amount'  => __('Amount'),
            'type'    => __('Type'),
            'gateway' => __('Gateway'),
            'status'  => __('Status'),
            'date'    => __('Date'),
        ];
    }

    public function smartpay_payment_column_data($column, $post_id)
    {
        switch ($column) {
            case 'id':
                echo $post_id;
                break;

            case 'name':
                $customer = get_post_meta($post_id, '_payment_customer_data', true);
                echo $customer['first_name'] ?? '' . ' ' . $customer['last_name'] ?? '';
                break;

            case 'email':
                echo get_post_meta($post_id, '_payment_email', true);
                break;

            case 'amount':
                echo smartpay_amount_format(get_post_meta($post_id, '_payment_amount', true), get_post_meta($post_id, '_payment_currency', true));
                break;

            case 'type':
                echo ucwords(str_replace('_', ' ', get_post_meta($post_id, '_payment_type', true)));
                break;

            case 'gateway':
                echo smartpay_payment_gateways()[get_post_meta($post_id, '_payment_gateway', true)]['admin_label'] ?? ucfirst(get_post_meta($post_id, '_payment_gateway', true));
                break;

            case 'status':
                echo ucfirst(get_post_status($post_id));
                break;

            default:
                break;
        }
    }

    public function modify_smartpay_payment_admin_table($actions, $post)
    {
        if ('smartpay_payment' === $post->post_type) {
            unset($actions['edit']);
            unset($actions['view']);
            // unset($actions['trash']);
            unset($actions['inline hide-if-no-js']);

            $actions = array_merge($actions, array(
                'manage' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url(admin_url('edit.php?post_type=smartpay_product&page=payment-details&id=' . $post->ID)),
                    'View details'
                )
            ));
        }

        return $actions;
    }

    public function update_payment_status()
    {
        if (!isset($_POST['smartpay_update_payment']) || !wp_verify_nonce($_POST['smartpay_update_payment'], 'smartpay_update_payment')) {
            return;
        }

        $payment_id     = $_POST['payment_id'] ?? 0;
        $payment_status = $_POST['payment_status'] ?? '';

        $payment = new SmartPay_Payment($payment_id);

        if (!$payment_id || !$payment->ID || empty($payment_status)) return;

        $payment->status = $payment_status;
        $payment->save();
    }
}
