<?php

namespace SmartPay\Admin;

use SmartPay\Customers\SmartPay_Customer;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Block_Editor
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Block_Editor class.
     *
     * @since 0.0.1
     */
    private function __construct()
    {
        add_action('init', [$this, 'product_block']);
    }

    /**
     * Main Block_Editor Instance.
     *
     * Ensures that only one instance of Block_Editor exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     *
     * @return object|Block_Editor
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Block_Editor)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function product_block()
    {
        $args = array(
            'post_type' => 'smartpay_product',
            'post_status' => 'publish',
            'nopaging' => true
        );
        $query = new \WP_Query($args);

        $products = array_map(function ($product) {
            return [
                'id' => $product->ID,
                'name' => $product->post_title
            ];
        }, $query->get_posts());

        wp_reset_postdata();

        wp_register_script('smartpay-product-block-js', SMARTPAY_PLUGIN_ASSETS . '/js/block-editor/product.js', ['wp-blocks']);

        register_block_type('smartpay/product', array(
            'editor_script' => 'smartpay-product-block-js',
        ));

        wp_localize_script('smartpay-product-block-js', 'smartpay_product_block_data', [
            'logo' => SMARTPAY_PLUGIN_ASSETS . '/img/logo.png',
            'products' => json_encode($products),
        ]);
    }
}