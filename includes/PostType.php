<?php

namespace ThemesGrove\SmartPay;

use ThemesGrove\SmartPay\Admin\Form\MetaBox;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class PostType
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct PostType class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'register_smartpay_form_post_type']);

        add_action('init', [$this, 'register_smartpay_payment_post_type']);

        add_filter('post_row_actions', [$this, 'remove_smartpay_post_type_inline_edit'], 10, 2);

        MetaBox::instance();
    }

    /**
     * Main PostType Instance.
     *
     * Ensures that only one instance of PostType exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|PostType
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof PostType)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function register_smartpay_form_post_type()
    {
        /** Payment Form Post Type */
        $labels = array(
            'name'                  => __('Payment Forms', 'wp-smartpay'),
            'singular_name'         => __('SmartPay Form', 'wp-smartpay'),
            'attributes'            => __('SmartPay Form Attributes', 'wp-smartpay'),
            'parent_item_colon'     => __('Parent SmartPay Form:', 'wp-smartpay'),
            'all_items'             => __('All SmartPay Form', 'wp-smartpay'),
            'add_new_item'          => __('Add New Payment Form', 'wp-smartpay'),
            'add_new'               => __('Add new', 'wp-smartpay'),
            'new_item'              => __('New SmartPay Form', 'wp-smartpay'),
            'edit_item'             => __('Edit SmartPay Form', 'wp-smartpay'),
            'update_item'           => __('Update SmartPay Form', 'wp-smartpay'),
            'view_item'             => __('View SmartPay Form', 'wp-smartpay'),
            'view_items'            => __('View SmartPay Forms', 'wp-smartpay'),
            'search_items'          => __('Search SmartPay Form', 'wp-smartpay'),
            'not_found'             => __('Not found', 'wp-smartpay'),
            'not_found_in_trash'    => __('Not found in Trash', 'wp-smartpay'),
            'featured_image'        => __('Featured Image', 'wp-smartpay'),
            'set_featured_image'    => __('Set featured image', 'wp-smartpay'),
            'remove_featured_image' => __('Remove featured image', 'wp-smartpay'),
            'use_featured_image'    => __('Use as featured image', 'wp-smartpay'),
            'insert_into_item'      => __('Insert into SmartPay Form', 'wp-smartpay'),
            'uploaded_to_this_item' => __('Uploaded to this SmartPay Form', 'wp-smartpay'),
            'items_list'            => __('SmartPay Forms list', 'wp-smartpay'),
            'items_list_navigation' => __('SmartPay Forms list navigation', 'wp-smartpay'),
            'filter_items_list'     => __('Filter SmartPay Forms list', 'wp-smartpay'),
        );

        $args = array(
            'label'                 => __('SmartPay Form', 'wp-smartpay'),
            'description'           => __('SmartPay Form', 'wp-smartpay'),
            'labels'                => $labels,
            'supports'              => array('title', 'thumbnail', 'revisions', 'author'),
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

        register_post_status('aggregated', array(
            'label'                     => _x('Aggregated ', 'post status label', 'bznrd'),
            'public'                    => true,
            'label_count'               => _n_noop('Aggregated s <span class="count">(%s)</span>', 'Aggregated s <span class="count">(%s)</span>', 'plugin-domain'),
            'post_type'                 => array('smartpay_form'),
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'show_in_metabox_dropdown'  => true,
            'show_in_inline_dropdown'   => true,
            'dashicon'                  => 'dashicons-businessman',
        ));
    }

    public function register_smartpay_payment_post_type()
    {
        /** Payment Post Type */
        $payment_labels = array(
            'name'               => _x('Payments', 'post type general name', 'wp-smartpay'),
            'singular_name'      => _x('Payment', 'post type singular name', 'wp-smartpay'),
            'add_new'            => __('Add New', 'wp-smartpay'),
            'add_new_item'       => __('Add New Payment', 'wp-smartpay'),
            'edit_item'          => __('Edit Payment', 'wp-smartpay'),
            'new_item'           => __('New Payment', 'wp-smartpay'),
            'all_items'          => __('All Payments', 'wp-smartpay'),
            'view_item'          => __('View Payment', 'wp-smartpay'),
            'search_items'       => __('Search Payments', 'wp-smartpay'),
            'not_found'          => __('No Payments found', 'wp-smartpay'),
            'not_found_in_trash' => __('No Payments found in Trash', 'wp-smartpay'),
            'parent_item_colon'  => '',
            'menu_name'          => __('Payment History', 'wp-smartpay')
        );

        $payment_args = array(
            'labels'          => $payment_labels,
            'public'          => true,
            'show_in_menu'    => false,
            'query_var'       => false,
            'rewrite'         => false,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
            'supports'        => [],
            'can_export'      => true,
            'capabilities' => array(
                'create_posts' => false
            )
        );
        register_post_type('smartpay_payment', $payment_args);
    }

    public function remove_smartpay_post_type_inline_edit($actions, $post)
    {
        if ('smartpay_form' === $post->post_type) {
            unset($actions['inline hide-if-no-js']);
        }

        if ('smartpay_payment' === $post->post_type) {
            unset($actions['edit']);
            unset($actions['view']);
            // unset($actions['trash']);
            unset($actions['inline hide-if-no-js']);

            $actions = array_merge($actions, array(
                // TODO:: MAke dynamic
                'manage' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url(''),
                    'View details'
                )
            ));
        }

        return $actions;
    }
}