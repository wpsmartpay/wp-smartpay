<?php

namespace SmartPay\Coupon;

use SmartPay\Models\Coupon;
use SmartPay\Support\ServiceProvider;

defined('ABSPATH') || exit;

final class CouponServiceProvider extends ServiceProvider
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct the class.
     *
     * @since  0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'register_post_type']);
    }

    /**
     * Main class Instance.
     *
     * Ensures that only one instance of the exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  0.0.1
     * @return object
     * @access public
     */
    public static function boot()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Coupon)) {
			self::$instance = new self();

        }

        return self::$instance;
    }

    /**
     * Register smartpay Coupon post type.
     *
     * @since  0.0.1
     * @access private
     */
    public function register_post_type()
    {
        $coupon_labels = array(
            'name'                  => __('Coupons', 'smartpay'),
            'singular_name'         => __('Coupon', 'smartpay'),
            'attributes'            => __('Coupon Attributes', 'smartpay'),
            'parent_item_colon'     => __('Parent Coupon:', 'smartpay'),
            'all_items'             => __('All Coupons', 'smartpay'),
            'add_new_item'          => __('Add New Coupon', 'smartpay'),
            'add_new'               => __('Add new', 'smartpay'),
            'new_item'              => __('New Coupon', 'smartpay'),
            'edit_item'             => __('Edit Coupon', 'smartpay'),
            'update_item'           => __('Update Coupon', 'smartpay'),
            'view_item'             => __('View Coupon', 'smartpay'),
            'view_items'            => __('View Coupons', 'smartpay'),
            'search_items'          => __('Search Coupon', 'smartpay'),
            'not_found'             => __('Not found', 'smartpay'),
            'not_found_in_trash'    => __('Not found in Trash', 'smartpay'),
            'featured_image'        => __('Featured Image', 'smartpay'),
            'set_featured_image'    => __('Set featured image', 'smartpay'),
            'remove_featured_image' => __('Remove featured image', 'smartpay'),
            'use_featured_image'    => __('Use as featured image', 'smartpay'),
            'insert_into_item'      => __('Insert into Coupon', 'smartpay'),
            'uploaded_to_this_item' => __('Uploaded to this Coupon', 'smartpay'),
            'items_list'            => __('Coupons list', 'smartpay'),
            'items_list_navigation' => __('Coupons list navigation', 'smartpay'),
            'filter_items_list'     => __('Filter Coupons list', 'smartpay'),
            'menu_name'             => __('SmartPay', 'smartpay'),
            'name_admin_bar'        => __('Coupon', 'smartpay')
        );

        $args = array(
            'label'                 => __('Coupon', 'smartpay'),
            'description'           => __('Coupon', 'smartpay'),
            'labels'                => $coupon_labels,
            'supports'              => array('title', 'revisions'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'query_var'             => true,
            'menu_icon'             => \smartpay_get_svg_icon_url(),
            'capability_type'       => 'page',
        );
		register_post_type('smartpay_coupon', $args);
	}

    public function get_coupon($coupon_id)
    {
        return new Coupon($coupon_id);
    }
}