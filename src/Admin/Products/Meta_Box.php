<?php

namespace SmartPay\Admin\Products;

use SmartPay\Products\SmartPay_Product;
use WP_Post;

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

    private $post;

    /**
     * Construct Meta_Box class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        // Add metabox.
        add_action('add_meta_boxes', [$this, 'add_smartpay_product_meta_boxes']);
        add_action('save_post_smartpay_product', [$this, 'save_smartpay_product_meta']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_smartpay_product_metabox_scripts']);
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

    public function add_smartpay_product_meta_boxes()
    {
        /** Metabox **/
        add_meta_box(
            'smartpay_product_metabox',
            __('SmartPay', 'smartpay'),
            [$this, 'render_metabox'],
            ['smartpay_product'],
            'normal',
            'high'
        );
    }

    public function render_metabox()
    {
        global $post;
        $product = new SmartPay_Product($post->ID);

        /** Output the price fields **/
        // ob_start();

        echo smartpay_view_render('admin/products/metabox', ['product' => $product]);

        // ob_get_clean();

        do_action('smartpay_product_metabox_fields', $post->ID);

        wp_nonce_field(basename(__FILE__), 'smartpay_product_metabox_nonce');
    }

    public function save_smartpay_product_meta($post_id)
    {
        if (!isset($_POST['smartpay_product_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_product_metabox_nonce'], basename(__FILE__))) {
            return;
        }
        extract($_POST);

        $product = new SmartPay_Product($post_id);
        $product->base_price     = $base_price;
        $product->sale_price     = $sale_price;
        $product->has_variations = (isset($has_variations) && 1 == $has_variations) ? true : false;
        $product->files          = isset($files) ? array_values($files) ?? [] : [];
        $product->save();

        if ($has_variations && isset($variations)) {

            remove_action('save_post_smartpay_product', [$this, 'save_smartpay_product_meta']);

            foreach ($variations as $variation_id => $variation) {

                $child_product = WP_Post::get_instance($variation_id ?? 0);

                if (!$child_product) {

                    $child_product                    = new SmartPay_Product();
                    $child_product->type              = 'variation';
                    $child_product->title             = $variation['name'] ?? '';
                    $child_product->description       = $variation['description'] ?? '';
                    $child_product->has_variations    = false;
                    $child_product->variations        = [];
                    $child_product->files             = array_keys($variation['files'] ?? []);
                    $child_product->parent            = $product->ID;
                    $child_product->additional_amount = $variation['additional_amount'] ?? 0;
                    $child_product->save();
                } else {
                    $child_product                    = new SmartPay_Product($variation_id ?? 0);
                    $child_product->type              = 'variation';
                    $child_product->title             = $variation['name'] ?? '';
                    $child_product->description       = $variation['description'] ?? '';
                    $child_product->has_variations    = false;
                    $child_product->variations        = [];
                    $child_product->files             = array_keys($variation['files'] ?? []);
                    $child_product->parent            = $product->ID;
                    $child_product->additional_amount = $variation['additional_amount'] ?? 0;
                    $child_product->save();
                }
            }

            add_action('save_post_smartpay_product', [$this, 'save_smartpay_product_meta']);
        }

        // Scope for other extentions
        do_action('save_smartpay_product', $post_id, $post);
    }

    public function enqueue_smartpay_product_metabox_scripts()
    {
        wp_enqueue_media();

        // Scripts
        wp_register_script('product-metabox', SMARTPAY_PLUGIN_ASSETS . '/js/product_metabox.js', '', SMARTPAY_VERSION);

        wp_enqueue_script('product-metabox');
    }
}