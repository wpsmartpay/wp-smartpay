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


        $coupon = new Coupon($post_id);
        $coupon->description     = $description;
        $coupon->discount_type   = $discount_type;
        $coupon->discount_amount = floatval($discount_amount);
		$coupon->expiry_date     = $expiry_date;
        $coupon->save();

        do_action('smartpay_save_coupon', $post_id, $coupon);
    }

}