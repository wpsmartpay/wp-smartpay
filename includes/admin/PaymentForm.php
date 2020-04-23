<?php

namespace ThemesGrove\SmartPay\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class PaymentForm
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct PaymentForm class.
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
     * Main PaymentForm Instance.
     *
     * Ensures that only one instance of PaymentForm exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|PaymentForm
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof PaymentForm)) {
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
            echo '<input type="text" readonly="readonly" title="Click to select. Then press Ctrl+C (⌘+C on Mac) to copy." onclick="this.select();" value="[smartpay_form id=&quot;'.$post_id.'&quot;]">';
        }

        // amount column
        if ('amount' === $column) {
            echo '$ '.number_format(get_post_meta($post_id, '_form_amount', true) ?? 0, 2);
        }
    }

    public function smartpay_payment_columns($columns)
    {
        return [
            'cb' => $columns['cb'],
            'id' => __('Payment ID'),
            'name' => __('Name'),
            'email' => __('Email'),
            'amount' => __('Amount'),
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
                echo get_post_meta($post_id, '_first_name', true).' '.get_post_meta($post_id, '_last_name', true);

                break;
            case 'email':
                echo get_post_meta($post_id, '_email', true);

                break;
            case 'amount':
                echo '$ '.number_format(get_post_meta($post_id, '_amount', true) ?? 0, 2);

                break;
            case 'status':
                echo ucfirst(get_post_status($post_id));

                break;
            default:
                break;
        }
    }
}
