<?php

namespace SmartPay\Downloads;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Download
{
	/**
	 * The single instance of this class
	 */
	private static $instance = null;

	/**
	 * Construct Download class.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function __construct()
	{
		add_action('init', [$this, 'register_smartpay_download_post_type']);

		add_filter('enter_title_here', [$this, 'change_default_title']);
	}

	/**
	 * Main Download Instance.
	 *
	 * Ensures that only one instance of Download exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 0.1
	 * @return object|Download
	 * @access public
	 */
	public static function instance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof Download)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register smartpay Download post type.
	 *
	 * @since 0.1
	 * @access private
	 */
	public function register_smartpay_Download_post_type()
	{
		$labels = array(
			'name'                  => __('Downloads', 'smartpay'),
			'singular_name'         => __('Download', 'smartpay'),
			'attributes'            => __('Download Attributes', 'smartpay'),
			'parent_item_colon'     => __('Parent Download:', 'smartpay'),
			'all_items'             => __('All Download', 'smartpay'),
			'add_new_item'          => __('Add New Download', 'smartpay'),
			'add_new'               => __('Add new', 'smartpay'),
			'new_item'              => __('New Download', 'smartpay'),
			'edit_item'             => __('Edit Download', 'smartpay'),
			'update_item'           => __('Update Download', 'smartpay'),
			'view_item'             => __('View Download', 'smartpay'),
			'view_items'            => __('View Downloads', 'smartpay'),
			'search_items'          => __('Search Download', 'smartpay'),
			'not_found'             => __('Not found', 'smartpay'),
			'not_found_in_trash'    => __('Not found in Trash', 'smartpay'),
			'featured_image'        => __('Featured Image', 'smartpay'),
			'set_featured_image'    => __('Set featured image', 'smartpay'),
			'remove_featured_image' => __('Remove featured image', 'smartpay'),
			'use_featured_image'    => __('Use as featured image', 'smartpay'),
			'insert_into_item'      => __('Insert into Download', 'smartpay'),
			'uploaded_to_this_item' => __('Uploaded to this Download', 'smartpay'),
			'items_list'            => __('Downloads list', 'smartpay'),
			'items_list_navigation' => __('Downloads list navigation', 'smartpay'),
            'filter_items_list'     => __('Filter Downloads list', 'smartpay'),
            'menu_name'             => __('SmartPay', 'smartpay'),
            'name_admin_bar'        => __('Download', 'smartpay')
		);

		$args = array(
			'label'                 => __('Download', 'smartpay'),
			'description'           => __('Download', 'smartpay'),
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
			'menu_icon'          => 'dashicons-media-default',
			'capability_type'       => 'page',


		);
		register_post_type('smartpay_download', $args);
	}

	/**
	 * Change default "Enter title here" input
	 *
	 * @since 1.4.0.2
	 * @param string $title Default title placeholder text
	 * @return string $title New placeholder text
	 */
	public function change_default_title($title)
	{
		if (!is_admin()) {
			$title = __('Enter download name here', 'smartpay');
			return $title;
		}

		$screen = get_current_screen();

		if ('smartpay_download' == $screen->post_type) {
			$title = __('Enter download name here', 'smartpay');
		}

		return $title;
	}
}