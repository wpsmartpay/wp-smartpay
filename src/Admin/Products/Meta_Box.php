<?php

namespace SmartPay\Admin\Products;

use SmartPay\Products\SmartPay_Product;
use SmartPay\Products\Product_Variation;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Meta_Box
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Meta_Box class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        // Add metabox.
        add_action('add_meta_boxes', [$this, 'add_product_meta_boxes']);

        add_action('save_post_product', [$this, 'save_product_meta']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_product_metabox_scripts']);

        add_action('admin_footer', [$this, 'admin_footer_scripts']);
    }

    /**
     * Main Meta_Box Instance.
     *
     * Ensures that only one instance of Meta_Box exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|Meta_Box
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Meta_Box)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add_product_meta_boxes()
    {
        /** Metabox **/
        add_meta_box(
            'product_metabox',
            __('Product Options', 'smartpay'),
            [$this, 'render_metabox'],
            ['product'],
            'normal',
            'high'
        );
    }

    public function render_metabox()
    {
        global $post;

        $product = new SmartPay_Product($post->ID);

        /** Output the metabox **/
        echo smartpay_view_render('admin/products/metabox', ['product' => $product]);

        do_action('smartpay_product_metabox_fields', $post->ID);

        wp_nonce_field(basename(__FILE__), 'smartpay_product_metabox_nonce');
    }

    public function save_product_meta($post_id)
    {
        if (!isset($_POST['smartpay_product_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_product_metabox_nonce'], basename(__FILE__))) {
            return;
        }

        extract($_POST);

        $product = new SmartPay_Product($post_id);
        $product->base_price     = floatval($base_price);
        $product->sale_price     = floatval($sale_price);
        $product->has_variations = (isset($has_variations) && 1 == $has_variations) ? true : false;
        $product->files          = isset($files) ? array_values($files) ?? [] : [];
        $product->save();

        if ($has_variations && isset($variations)) {

            foreach ($variations as $variation_id => $variation) {
                $child_product                    = new Product_Variation($variation_id ?? 0);
                $child_product->name              = $variation['name'] ?? '';
                $child_product->description       = $variation['description'] ?? '';
                $child_product->files             = array_keys($variation['files'] ?? []);
                $child_product->parent            = $product->ID;
                $child_product->additional_amount = floatval($variation['additional_amount'] ?? 0);
                $child_product->save();
            }
        }

        // Scope for other extentions
        do_action('smartpay_save_product', $post_id, $post);
    }

    public function enqueue_product_metabox_scripts()
    {
        wp_enqueue_media();

        // Scripts
        wp_register_script('product-metabox', SMARTPAY_PLUGIN_ASSETS . '/js/product_metabox.js', '', SMARTPAY_VERSION);

        wp_enqueue_script('product-metabox');
    }

    public function admin_footer_scripts()
    {
        global $post;
        if ($post && $post->post_type == 'product') {
            echo '<script> document.getElementById("edit-slug-box").outerHTML = ""; </script>';
        }
    }
}