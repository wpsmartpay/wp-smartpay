<?php

namespace SmartPay\Admin\Coupon;

use SmartPay\Models\Coupon;

defined('ABSPATH') || exit;

final class MetaBox
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);

        add_action('save_post_smartpay_coupon', [$this, 'save_meta']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'coupon_metabox',
            __('Coupon Options', 'smartpay'),
            [$this, 'render_metabox'],
            ['smartpay_coupon'],
            'normal',
            'high'
        );
    }

    public function render_metabox()
    {
        global $post;

        $coupon = new Coupon($post->ID);

        echo smartpay_view_render('admin/coupon/metabox', ['coupon' => $coupon]);

        do_action('smartpay_coupon_metabox_fields', $post->ID);

        wp_nonce_field('smartpay_coupon_metabox_nonce', 'smartpay_coupon_metabox_nonce');
    }

    public function save_meta($post_id)
    {
        if (!isset($_POST['smartpay_coupon_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_coupon_metabox_nonce'], 'smartpay_coupon_metabox_nonce')) {
            return;
        }

		extract(sanitize_post($_POST));

		echo '<pre>';
		var_dump($_POST); die();

        // $product = new Coupon($post_id);
        // $product->base_price     = floatval($base_price);
        // $product->sale_price     = floatval($sale_price);
        // $product->has_variations = (isset($has_variations) && 1 == $has_variations) ? true : false;
        // $product->files          = isset($files) ? array_values($files) ?? [] : [];
        // $product->save();

        // if ($has_variations && isset($variations)) {

        //     foreach ($variations as $variation_id => $variation) {
        //         // TODO: Check validation and if false then ignore
        //         $child_product                    = new Product_Variation($variation_id ?? 0);
        //         $child_product->name              = $variation['name'] ?? '';
        //         $child_product->description       = $variation['description'] ?? '';
        //         $child_product->files             = array_keys($variation['files'] ?? []);
        //         $child_product->parent            = $product->ID;
        //         $child_product->additional_amount = floatval($variation['additional_amount'] ?? 0);
        //         $child_product->save();
        //     }
        // }

        // // Scope for other extentions
        // do_action('smartpay_save_product', $post_id, $product);
    }

    public function enqueue_product_metabox_scripts()
    {
    //     wp_enqueue_media();

    //     // Scripts
    //     wp_register_script('product-metabox', SMARTPAY_PLUGIN_ASSETS . '/js/product_metabox.js', '', SMARTPAY_VERSION);

    //     wp_enqueue_script('product-metabox');
    }
}