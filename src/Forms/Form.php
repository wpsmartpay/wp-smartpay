<?php

namespace SmartPay\Forms;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Form
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Form class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'register_smartpay_form_post_type']);
    }

    /**
     * Main Form Instance.
     *
     * Ensures that only one instance of Form exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Form
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Form)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function register_smartpay_form_post_type()
    {
        /** Payment Form Post Type */
        $labels = array(
            'name'                  => __('Payment Forms', 'smartpay'),
            'singular_name'         => __('SmartPay Form', 'smartpay'),
            'attributes'            => __('SmartPay Form Attributes', 'smartpay'),
            'parent_item_colon'     => __('Parent Form:', 'smartpay'),
            'all_items'             => __('All Form', 'smartpay'),
            'add_new_item'          => __('Add New Payment Form', 'smartpay'),
            'add_new'               => __('Add new', 'smartpay'),
            'new_item'              => __('New Form', 'smartpay'),
            'edit_item'             => __('Edit Form', 'smartpay'),
            'update_item'           => __('Update Form', 'smartpay'),
            'view_item'             => __('View Form', 'smartpay'),
            'view_items'            => __('View Forms', 'smartpay'),
            'search_items'          => __('Search Form', 'smartpay'),
            'not_found'             => __('Not found', 'smartpay'),
            'not_found_in_trash'    => __('Not found in Trash', 'smartpay'),
            'featured_image'        => __('Featured Image', 'smartpay'),
            'set_featured_image'    => __('Set featured image', 'smartpay'),
            'remove_featured_image' => __('Remove featured image', 'smartpay'),
            'use_featured_image'    => __('Use as featured image', 'smartpay'),
            'insert_into_item'      => __('Insert into Form', 'smartpay'),
            'uploaded_to_this_item' => __('Uploaded to this Form', 'smartpay'),
            'items_list'            => __('Forms list', 'smartpay'),
            'items_list_navigation' => __('Forms list navigation', 'smartpay'),
            'filter_items_list'     => __('Filter Forms list', 'smartpay'),
        );

        $args = array(
            'label'                 => __('SmartPay Form', 'smartpay'),
            'description'           => __('SmartPay Form', 'smartpay'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'revisions'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
        );
        register_post_type('smartpay_form', $args);
    }

    public function get_form($form_id)
    {
        return new SmartPay_Form($form_id);
    }
}