<?php

namespace SmartPay\Product;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Product
{
	/**
	 * The single instance of this class
	 */
	private static $instance = null;

	/**
	 * Construct Product class.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function __construct()
	{
		add_action('init', [$this, 'register_smartpay_product_post_type']);
	}

	/**
	 * Main Product Instance.
	 *
	 * Ensures that only one instance of Product exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 0.1
	 * @return object|Product
	 * @access public
	 */
	public static function instance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof Product)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register smartpay product post type.
	 *
	 * @since 0.1
	 * @access private
	 */
	public function register_smartpay_product_post_type()
	{
		$labels = array(
			'name'                  => __('Products', 'smartpay'),
			'singular_name'         => __('Product', 'smartpay'),
			'attributes'            => __('Product Attributes', 'smartpay'),
			'parent_item_colon'     => __('Parent Product:', 'smartpay'),
			'all_items'             => __('All Product', 'smartpay'),
			'add_new_item'          => __('Add New Payment Form', 'smartpay'),
			'add_new'               => __('Add new', 'smartpay'),
			'new_item'              => __('New Product', 'smartpay'),
			'edit_item'             => __('Edit Product', 'smartpay'),
			'update_item'           => __('Update Product', 'smartpay'),
			'view_item'             => __('View Product', 'smartpay'),
			'view_items'            => __('View Products', 'smartpay'),
			'search_items'          => __('Search Product', 'smartpay'),
			'not_found'             => __('Not found', 'smartpay'),
			'not_found_in_trash'    => __('Not found in Trash', 'smartpay'),
			'featured_image'        => __('Featured Image', 'smartpay'),
			'set_featured_image'    => __('Set featured image', 'smartpay'),
			'remove_featured_image' => __('Remove featured image', 'smartpay'),
			'use_featured_image'    => __('Use as featured image', 'smartpay'),
			'insert_into_item'      => __('Insert into Product', 'smartpay'),
			'uploaded_to_this_item' => __('Uploaded to this Product', 'smartpay'),
			'items_list'            => __('Products list', 'smartpay'),
			'items_list_navigation' => __('Products list navigation', 'smartpay'),
			'filter_items_list'     => __('Filter Products list', 'smartpay'),
		);

		$args = array(
			'label'                 => __('Product', 'smartpay'),
			'description'           => __('Product', 'smartpay'),
			'labels'                => $labels,
			'supports'              => array('title', 'editor', 'thumbnail', 'revisions', 'author'),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'query_var'          => true,
			'menu_icon'          => 'dashicons-download',
			'capability_type'       => 'page',


		);
		register_post_type('smartpay_product', $args);
	}
}