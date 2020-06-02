<?php

namespace SmartPay\Admin\Forms;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Form
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Form class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        add_filter('manage_smartpay_form_posts_columns', [$this, 'smartpay_form_columns']);

        add_filter('manage_smartpay_form_posts_custom_column', [$this, 'smartpay_form_column_data'], 10, 2);

        add_filter('post_row_actions', [$this, 'modify_smartpay_form_admin_table'], 10, 2);
    }

    /**
     * Main Form Instance.
     *
     * Ensures that only one instance of Form exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|Form
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Form)) {
            self::$instance = new self();
            self::$instance->meta_box   = Meta_Box::instance();
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

    public function modify_smartpay_form_admin_table($actions, $post)
    {
        if ('smartpay_form' === $post->post_type) {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }
}