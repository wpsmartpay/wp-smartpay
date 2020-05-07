<?php

namespace SmartPay\Admin\Downloads;

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
        add_action('add_meta_boxes', [$this, 'add_smartpay_download_meta_boxes']);
        add_action('save_post', [$this, 'save_smartpay_download_meta']);
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

    public function add_smartpay_download_meta_boxes()
    {
		 /** Contents **/
		 add_meta_box(
            'smartpay_download_contents',
            __('Files', 'smartpay'),
            [$this, 'render_smartpay_download_files_meta_box'],
            ['smartpay_download'],
            'normal',
            'high'
		);

        /** Pricing **/
        add_meta_box(
            'smartpay_download_pricing',
            __('Pricing', 'smartpay'),
            [$this, 'render_smartpay_download_pricing_meta_box'],
            ['smartpay_download'],
            'normal',
            'high'
        );

        // /** Variant **/
        // add_meta_box(
        //     'smartpay_download_variant',
        //     __('Variant', 'smartpay'),
        //     [$this, 'render_smartpay_download_variants_meta_box'],
        //     ['smartpay_download'],
        //     'normal',
        //     'high'
        // );
    }

    public function render_smartpay_download_pricing_meta_box()
    {
        global $post;

        /** Output the price fields **/
        // ob_start();

        echo smartpay_view_render('admin/downloads/metabox/pricing', ['post', $post]);

        // ob_get_clean();

        do_action('smartpay_download_pricing_meta_box_fields', $post->ID);

        wp_nonce_field(basename(__FILE__), 'smartpay_download_meta_box_nonce');
    }

    public function render_smartpay_download_files_meta_box()
    {
        global $post;

        /** Output the price fields **/
        // ob_start();

        echo smartpay_view_render('admin/downloads/metabox/files', ['post', $post]);

        // ob_get_clean();

        do_action('smartpay_download_contents_meta_box_fields', $post->ID);
    }

    public function render_smartpay_download_variants_meta_box()
    {
        global $post;

        /** Output the price fields **/
        // ob_start();

        echo smartpay_view_render('admin/downloads/metabox/variants', ['post', $post]);

        // ob_get_clean();

        do_action('smartpay_download_variant_meta_box_fields', $post->ID);
    }

















    public function save_smartpay_download_meta($post_id)
    {
        if (!isset($_POST['smartpay_download_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_download_metabox_nonce'], 'smartpay_download_metabox_nonce')) {
            return;
        }

        if (isset($_POST['_form_payment_type'])) {
            \update_post_meta($post_id, '_form_payment_type', sanitize_text_field($_POST['_form_payment_type']));
        }

        if (isset($_POST['_form_amount'])) {
            \update_post_meta($post_id, '_form_amount', sanitize_text_field($_POST['_form_amount']));
        }

        if (isset($_POST['_form_payment_button_text'])) {
            \update_post_meta($post_id, '_form_payment_button_text', sanitize_text_field($_POST['_form_payment_button_text']));
        }

        if (isset($_POST['_form_payment_button_processing_text'])) {
            \update_post_meta($post_id, '_form_payment_button_processing_text', sanitize_text_field($_POST['_form_payment_button_processing_text']));
        }

        if (isset($_POST['_form_payment_button_style'])) {
            \update_post_meta($post_id, '_form_payment_button_style', sanitize_text_field($_POST['_form_payment_button_style']));
        }

        if (isset($_POST['_form_paddle_checkout_image'])) {
            \update_post_meta($post_id, '_form_paddle_checkout_image', sanitize_text_field($_POST['_form_paddle_checkout_image']));
        }

        if (isset($_POST['_form_paddle_checkout_location'])) {
            \update_post_meta($post_id, '_form_paddle_checkout_location', sanitize_text_field($_POST['_form_paddle_checkout_location']));
        }
    }
}
