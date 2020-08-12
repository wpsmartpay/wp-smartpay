<?php

namespace SmartPay\Admin\Products;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Product
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Product class.
     *
     * @since 0.0.1
     */
    private function __construct()
    {
        add_filter('enter_title_here', [$this, 'change_default_title']);

        add_filter('manage_smartpay_product_posts_columns', [$this, 'product_columns']);

        add_filter('manage_smartpay_product_posts_custom_column', [$this, 'product_column_data'], 10, 2);

        add_filter('post_row_actions', [$this, 'modify_admin_table'], 10, 2);
    }

    /**
     * Main Product Instance.
     *
     * Ensures that only one instance of Product exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     *
     * @return object|Product
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Product)) {
            self::$instance = new self();
            self::$instance->meta_box   = Meta_Box::instance();
        }

        return self::$instance;
    }


    /**
     * Change default "Enter title here" input
     *
     * @since 0.0.1
     * @param string $title Default title placeholder text
     * @return string $title New placeholder text
     */
    public function change_default_title($title)
    {
        if (!is_admin()) {
            $title = __('Enter product name here', 'smartpay');
            return $title;
        }

        $screen = get_current_screen();

        if ('smartpay_product' == $screen->post_type) {
            $title = __('Enter product name here', 'smartpay');
        }

        return $title;
    }

    public function product_columns($columns)
    {
        return [
            'cb' => $columns['cb'],
            'title' => __('Title'),
            'shortcode' => __('Shortcode'),
            'date' => __('Date'),
        ];
    }

    public function product_column_data($column, $post_id)
    {
        // shortcode column
        if ('shortcode' === $column) {
            echo '<input type="text" readonly="readonly" title="Click to select. Then press Ctrl+C (⌘+C on Mac) to copy." onclick="this.select();" value="[smartpay_product id=&quot;' . $post_id . '&quot;]">';
        }
    }

    public function modify_admin_table($actions, $post)
    {
        if ('smartpay_product' === $post->post_type) {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }
}