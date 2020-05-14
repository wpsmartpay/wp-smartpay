<?php

namespace SmartPay;

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

        add_filter('post_row_actions', [$this, 'remove_smartpay_post_type_inline_edit'], 10, 2);
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
            'name'                  => __('Payment Forms', 'smartpay'),
            'singular_name'         => __('SmartPay Form', 'smartpay'),
            'attributes'            => __('SmartPay Form Attributes', 'smartpay'),
            'parent_item_colon'     => __('Parent SmartPay Form:', 'smartpay'),
            'all_items'             => __('All SmartPay Form', 'smartpay'),
            'add_new_item'          => __('Add New Payment Form', 'smartpay'),
            'add_new'               => __('Add new', 'smartpay'),
            'new_item'              => __('New SmartPay Form', 'smartpay'),
            'edit_item'             => __('Edit SmartPay Form', 'smartpay'),
            'update_item'           => __('Update SmartPay Form', 'smartpay'),
            'view_item'             => __('View SmartPay Form', 'smartpay'),
            'view_items'            => __('View SmartPay Forms', 'smartpay'),
            'search_items'          => __('Search SmartPay Form', 'smartpay'),
            'not_found'             => __('Not found', 'smartpay'),
            'not_found_in_trash'    => __('Not found in Trash', 'smartpay'),
            'featured_image'        => __('Featured Image', 'smartpay'),
            'set_featured_image'    => __('Set featured image', 'smartpay'),
            'remove_featured_image' => __('Remove featured image', 'smartpay'),
            'use_featured_image'    => __('Use as featured image', 'smartpay'),
            'insert_into_item'      => __('Insert into SmartPay Form', 'smartpay'),
            'uploaded_to_this_item' => __('Uploaded to this SmartPay Form', 'smartpay'),
            'items_list'            => __('SmartPay Forms list', 'smartpay'),
            'items_list_navigation' => __('SmartPay Forms list navigation', 'smartpay'),
            'filter_items_list'     => __('Filter SmartPay Forms list', 'smartpay'),
        );

        $args = array(
            'label'                 => __('SmartPay Form', 'smartpay'),
            'description'           => __('SmartPay Form', 'smartpay'),
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

            // $actions = array_merge($actions, array(
            // TODO:: Make dynamic
            //     'manage' => sprintf(
            //         '<a href="%1$s">%2$s</a>',
            //         esc_url(''),
            //         'View details'
            //     )
            // ));
        }

        return $actions;
    }
}