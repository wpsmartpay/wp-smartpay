<?php

namespace ThemesGrove\SmartPay\Admin\Form;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class MetaBox
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct MetaBox class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        // Add metabox.
        add_action('add_meta_boxes', [$this, 'add_smartpay_form_meta_box']);
        add_action('save_post', [$this, 'save_smartpay_form_meta']);
    }

    /**
     * Main MetaBox Instance.
     *
     * Ensures that only one instance of MetaBox exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|MetaBox
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof MetaBox)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add_smartpay_form_meta_box()
    {
        add_meta_box(
            'smartpay-form-metabox-data',
            'Payment Form Options',
            [$this, 'add_smartpay_form_meta_box_callback'],
            ['smartpay_form'],
            'normal',
            'high'
        );
    }

    public function add_smartpay_form_meta_box_callback($post)
    {
        return view('admin/form/payment_form_metabox', ['post' => $post]);
    }

    public function save_smartpay_form_meta($post_id)
    {
        // die(var_dump($post_id));

        if (!isset($_POST['smartpay_form_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_form_metabox_nonce'], 'smartpay_form_metabox_nonce')) {
            return;
        }

        if (isset($_POST['_form_amount'])) {
            update_post_meta($post_id, '_form_amount', sanitize_text_field($_POST['_form_amount']));
        }
    }
}