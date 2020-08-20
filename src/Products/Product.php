<?php

namespace SmartPay\Products;

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
     * @since  0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'register_products_post_type']);

        // Page template for product
        add_filter('single_template', [$this, 'smartpay_product_page_template']);
    }

    /**
     * Main Product Instance.
     *
     * Ensures that only one instance of Product exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  0.0.1
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
     * Page template for smartpay_product
     * 
     * @since  0.0.5
     * @param string $template
     */
    public function smartpay_product_page_template($template)
    {
        global $post;

        if (is_singular('smartpay_product') && !locate_template('single-smartpay_product.php')) {
            $template = SMARTPAY_DIR . '/includes/views/page-templates/single-smartpay_product.php';
        }

        return $template;
    }

    /**
     * Register smartpay Product post type.
     *
     * @since  0.0.1
     * @access private
     */
    public function register_products_post_type()
    {
        $product_labels = array(
            'name'                  => __('Products', 'smartpay'),
            'singular_name'         => __('Product', 'smartpay'),
            'attributes'            => __('Product Attributes', 'smartpay'),
            'parent_item_colon'     => __('Parent Product:', 'smartpay'),
            'all_items'             => __('All Product', 'smartpay'),
            'add_new_item'          => __('Add New Product', 'smartpay'),
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
            'menu_name'             => __('SmartPay', 'smartpay'),
            'name_admin_bar'        => __('Product', 'smartpay')
        );

        $args = array(
            'label'                 => __('Product', 'smartpay'),
            'description'           => __('Product', 'smartpay'),
            'labels'                => $product_labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'revisions'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
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
        register_post_type('smartpay_product', $args);

        $args = array(
            'label'                 => __('Product variation', 'smartpay'),
            'description'           => __('Product variation', 'smartpay'),
            'labels'                => [],
            'supports'              => array('title', 'editor'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => false,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'query_var'             => true,
            'capability_type'       => 'page',
        );
        register_post_type('sp_product_variation', $args);
    }

    public function get_product($product_id)
    {
        return new SmartPay_Product($product_id);
    }

    public function get_product_variation($variation_id)
    {
        return new Product_Variation($variation_id);
    }
}