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
        add_action('init', [$this, 'smartpay_block_editors']);
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

    private function get_data($type = 'smartpay_product')
    {
        $args = array(
            'post_type' => $type,
            'post_status' => 'publish',
            'nopaging' => true
        );
        $query = new \WP_Query($args);

        $data = array_map(function ($product) {
            return [
                'id' => $product->ID,
                'name' => $product->post_title
            ];
        }, $query->get_posts());

        wp_reset_postdata();

        return $data;
    }

    private function product_block()
    {
        register_block_type('smartpay/product', array(
            'editor_script' => 'smartpay-block-editors-js',
        ));

        wp_localize_script('smartpay-block-editors-js', 'smartpay_block_editor_products', json_encode($this->get_data()));
    }

    private function form_block()
    {
        register_block_type('smartpay/form', array(
            'editor_script' => 'smartpay-block-editors-js',
        ));

        wp_localize_script('smartpay-block-editors-js', 'smartpay_block_editor_forms', json_encode($this->get_data('smartpay_form')));
    }

    public function smartpay_block_editors()
    {
        wp_register_script('smartpay-block-editors-js', SMARTPAY_PLUGIN_ASSETS . '/js/blocks/index.js', ['wp-element', 'wp-plugins', 'wp-blocks', 'wp-edit-post']);

        // SmartPay logo
        wp_localize_script('smartpay-block-editors-js', 'smartpay_logo', SMARTPAY_PLUGIN_ASSETS . '/img/logo.png');

        // Product blocks
        $this->product_block();

        // Product blocks
        $this->form_block();
    }
}