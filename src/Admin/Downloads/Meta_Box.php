<?php

namespace SmartPay\Admin\Downloads;

use SmartPay\Downloads\SmartPay_Download;

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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_smartpay_download_metabox_scripts']);
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
        /** Metabox **/
        add_meta_box(
            'smartpay_download_metabox',
            __('SmartPay', 'smartpay'),
            [$this, 'render_metabox'],
            ['smartpay_download'],
            'normal',
            'high'
        );
    }

    public function render_metabox()
    {
        global $post;
        $download = new SmartPay_Download($post->ID);

        /** Output the price fields **/
        // ob_start();

        echo smartpay_view_render('admin/downloads/metabox', ['download' => $download]);

        // ob_get_clean();

        do_action('smartpay_download_metabox_fields', $post->ID);

        wp_nonce_field(basename(__FILE__), 'smartpay_download_metabox_nonce');
    }

    public function save_smartpay_download_meta($post_id)
    {
        if (!isset($_POST['smartpay_download_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_download_metabox_nonce'], basename(__FILE__))) {
            return;
        }

        extract($_POST);
        // var_dump($variations);
        // exit();

        // Base price
        if (isset($base_price)) {
            update_post_meta($post_id, '_smartpay_base_price', sanitize_text_field($base_price));
        }

        // Sale price
        if (isset($sale_price)) {
            update_post_meta($post_id, '_smartpay_sale_price', sanitize_text_field($sale_price));
        }

        // Files
        if (isset($files)) {
            update_post_meta($post_id, '_smartpay_download_files', $files ?? []);
        } else {
            update_post_meta($post_id, '_smartpay_download_files', []);
        }

        // Variation
        $smartpay_variations = [];
        foreach ($variations as $index => $variation) {
            $_variant = array(
                'title' => $variation['title'] ?? 'Variation ' . $index + 1,
                'name' => $variation['name'] ?? 'Variation ' . $index + 1,
                'additional_amount' => $variation['additional_amount'] ?? 0,
                'description' => $variation['description'] ?? '',
                'files' => $variation['files'] ?? [],
            );
            array_push($smartpay_variations, apply_filters('smartpay_download_variation', $_variant));
        }

        update_post_meta($post_id, '_smartpay_variations', $smartpay_variations);

        do_action('save_smartpay_download', $post_id, $post);
    }

    public function enqueue_smartpay_download_metabox_scripts()
    {
        wp_enqueue_media();

        // Scripts
        wp_register_script('smartpay-download-file-selector', SMARTPAY_PLUGIN_ASSETS . '/js/download_file_selector.js', '', SMARTPAY_VERSION);

        wp_enqueue_script('smartpay-download-file-selector');
    }
}
