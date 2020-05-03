<?php

namespace ThemesGrove\SmartPay\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Payment_Form
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Payment_Form class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        add_filter('manage_smartpay_form_posts_columns', [$this, 'smartpay_form_columns']);
        add_filter('manage_smartpay_form_posts_custom_column', [$this, 'smartpay_form_column_data'], 10, 2);

        add_filter('manage_smartpay_payment_posts_columns', [$this, 'smartpay_payment_columns']);
        add_filter('manage_smartpay_payment_posts_custom_column', [$this, 'smartpay_payment_column_data'], 10, 2);
    }

    /**
     * Main Payment_Form Instance.
     *
     * Ensures that only one instance of Payment_Form exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|Payment_Form
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Payment_Form)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function smartpay_form_columns($columns)
    {
        return [
            'cb' => $columns['cb'],
            'title' => __('Title'),
            'shortcode' => __('Shortcode'),
            'amount' => __('Amount'),
            'date' => __('Date'),
        ];
    }

    public function smartpay_form_column_data($column, $post_id)
    {
        // shortcode column
        if ('shortcode' === $column) {
            echo '<input type="text" readonly="readonly" title="Click to select. Then press Ctrl+C (⌘+C on Mac) to copy." onclick="this.select();" value="[smartpay_form id=&quot;' . $post_id . '&quot;]">';
        }

        // amount column
        if ('amount' === $column) {
            echo smartpay_amount_format(get_post_meta($post_id, '_form_amount', true));
        }
    }

    // TODO: Add seperated class

    public function smartpay_payment_columns($columns)
    {
        return [
            'cb' => $columns['cb'],
            'id' => __('Payment ID'),
            'name' => __('Name'),
            'email' => __('Email'),
            'amount' => __('Amount'),
            'gateway' => __('Gateway'),
            'status' => __('Status'),
            'date' => __('Date'),
        ];
    }

    public function smartpay_payment_column_data($column, $post_id)
    {
        switch ($column) {
            case 'id':
                echo $post_id;
                break;

            case 'name':
                echo get_post_meta($post_id, '_smartpay_payment_first_name', true) . ' ' . get_post_meta($post_id, '_smartpay_payment_last_name', true);
                break;

            case 'email':
                echo get_post_meta($post_id, '_smartpay_payment_email', true);
                break;

            case 'amount':
                echo smartpay_amount_format(get_post_meta($post_id, '_smartpay_payment_amount', true), get_post_meta($post_id, '_smartpay_payment_currency', true));
                break;

            case 'gateway':
                echo smartpay_payment_gateways()[get_post_meta($post_id, '_smartpay_payment_gateway', true)]['admin_label'] ?? ucfirst(get_post_meta($post_id, '_smartpay_payment_gateway', true));
                break;

            case 'status':
                echo ucfirst(get_post_status($post_id));
                break;

            default:
                break;
        }
    }
}