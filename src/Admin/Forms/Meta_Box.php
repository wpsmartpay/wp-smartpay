<?php

namespace SmartPay\Admin\Forms;

use SmartPay\Forms\SmartPay_Form;

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
        add_action('add_meta_boxes', [$this, 'add_form_meta_boxes']);

        add_action('save_post', [$this, 'save_form_meta']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_form_metabox_scripts']);

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
        if (!isset(self::$instance) && !(self::$instance instanceof MetaBox)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add_form_meta_boxes()
    {
        /** Metabox **/
        add_meta_box(
            'form_metabox',
            __('Form Options', 'smartpay'),
            [$this, 'render_metabox'],
            ['smartpay_form'],
            'normal',
            'high'
        );
    }

    public function render_metabox($post)
    {
        global $post;

        $form = new SmartPay_Form($post->ID);

        /** Output the metabox **/
        echo smartpay_view_render('admin/form/metabox', ['form' => $form]);

        do_action('smartpay_form_metabox_fields', $post->ID);

        wp_nonce_field(basename(__FILE__), 'smartpay_form_metabox_nonce');
    }

    public function save_form_meta($post_id)
    {
        if (!isset($_POST['smartpay_form_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_form_metabox_nonce'], basename(__FILE__))) {
            return;
        }

        extract($_POST);

        $form = new SmartPay_Form($post_id);
        $form->base_price     = $base_price;
        $form->sale_price     = $sale_price;
        $form->has_variations = (isset($has_variations) && 1 == $has_variations) ? true : false;
        $form->files          = isset($files) ? array_values($files) ?? [] : [];
        $form->save();

        if ($has_variations && isset($variations)) {

            foreach ($variations as $variation_id => $variation) {
                $child_form                    = new form_Variation($variation_id ?? 0);
                $child_form->name              = $variation['name'] ?? '';
                $child_form->description       = $variation['description'] ?? '';
                $child_form->files             = array_keys($variation['files'] ?? []);
                $child_form->parent            = $form->ID;
                $child_form->additional_amount = $variation['additional_amount'] ?? 0;
                $child_form->save();
            }
        }

        // Scope for other extentions
        do_action('smartpay_save_form', $post_id, $post);
    }

    public function enqueue_form_metabox_scripts()
    {
        wp_enqueue_media();

        // Scripts
        wp_register_script('form-metabox', SMARTPAY_PLUGIN_ASSETS . '/js/form_metabox.js', '', SMARTPAY_VERSION);

        wp_enqueue_script('form-metabox');
    }

    public function admin_footer_scripts()
    {
        global $post;
        if ($post && $post->post_type == 'smartpay_form') {
            echo '<script> document.getElementById("edit-slug-box").outerHTML = ""; </script>';
        }
    }
}