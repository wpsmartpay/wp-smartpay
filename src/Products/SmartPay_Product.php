<?php

namespace SmartPay\Products;

use WP_Error;
use WP_Post;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Product
{

    /**
     * The product ID
     *
     * @since 0.1
     */
    public $ID = 0;

    protected $status = 'publish';

    /**
     * The product base price
     *
     * @since 0.1
     */
    private $base_price;

    /**
     * The product sale price
     *
     * @since 0.1
     */
    private $sale_price;

    /**
     * The product variations
     *
     * @since 0.1
     */
    private $variations = array();

    /**
     * The product files
     *
     * @since 0.1
     */
    private $files;

    /**
     * The product's sale count
     *
     * @since 0.1
     */
    private $sales;

    /**
     * The product's notes
     *
     * @since 0.1
     */
    private $notes;

    /**
     * The product sku
     *
     * @since 0.1
     */
    private $sku;

    /**
     * Declare the default properties in WP_Post as we can't extend it
     * Anything we've declared above has been removed.
     */
    public $post_author = 0;
    public $post_date = '0000-00-00 00:00:00';
    public $post_date_gmt = '0000-00-00 00:00:00';
    public $post_content = '';
    public $post_title = '';
    public $post_status = 'publish';
    public $comment_status = 'closed';
    public $ping_status = 'closed';
    public $post_password = '';
    public $post_name = '';
    public $to_ping = '';
    public $pinged = '';
    public $post_modified = '0000-00-00 00:00:00';
    public $post_modified_gmt = '0000-00-00 00:00:00';
    public $post_content_filtered = '';
    public $post_parent = 0;
    public $guid = '';
    public $menu_order = 0;
    public $post_mime_type = '';
    public $comment_count = 0;
    public $filter;

    /**
     * Get things going
     *
     * @since 0.1
     */
    public function __construct($_id = false)
    {
        if (!$_id) {
            return;
        }

        $product = WP_Post::get_instance($_id);

        return $this->setup_product($product);
    }

    /**
     * Given the product data, let's set the variables
     *
     * @since  2.3.6
     * @param  WP_Post $product The WP_Post object for product.
     * @return bool             If the setup was successful or not
     */
    private function setup_product($product)
    {

        if (!is_object($product)) {
            return false;
        }

        if (!$product instanceof WP_Post) {
            return false;
        }

        if ('smartpay_product' !== $product->post_type) {
            return false;
        }

        foreach ($product as $key => $value) {

            switch ($key) {

                default:
                    $this->$key = $value;
                    break;
            }
        }

        // TODO: Set all other data
        $this->status = $product->post_status;

        return true;
    }

    /**
     * Magic __get function to dispatch a call to retrieve a private property
     *
     * @since 0.1
     */
    public function __get($key)
    {

        if (method_exists($this, 'get_' . $key)) {

            return call_user_func(array($this, 'get_' . $key));
        } else {

            return new WP_Error('smartpay-product-invalid-property', sprintf(__('Can\'t get property %s', 'smartpay'), $key));
        }
    }

    /**
     * Creates a product
     *
     * @since  2.3.6
     * @param  array  $data Array of attributes for a product
     * @return mixed  false if data isn't passed and class not instantiated for creation, or New product ID
     */
    public function create($data = array())
    {
        if (0 != $this->id) {
            return false;
        }

        $defaults = array(
            'post_type'   => 'smartpay_product',
            'post_status' => 'draft',
            'post_title'  => __('New Product', 'smartpay')
        );

        $args = wp_parse_args($data, $defaults);

        /**
         * Fired before a product is created
         *
         * @param array $args The post object arguments used for creation.
         */
        do_action('smartpay_product_pre_create', $args);

        $id = wp_insert_post($args, true);

        $product = WP_Post::get_instance($id);

        /**
         * Fired after a product is created
         *
         * @param int   $id   The post ID of the created item.
         * @param array $args The post object arguments used for creation.
         */
        do_action('smartpay_product_post_create', $id, $args);

        return $this->setup_product($product);
    }

    /**
     * Retrieve the ID
     *
     * @since 0.1
     * @return int ID of the product
     */
    public function get_ID()
    {
        return $this->ID;
    }

    /**
     * Retrieve the product name
     *
     * @since 2.5.8
     * @return string Name of the product
     */
    public function get_name()
    {
        return get_the_title($this->ID);
    }

    /**
     * Retrieve the product status
     *
     * @since 2.5.8
     * @return string Status of the product
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * Retrieve the product description
     *
     * @since 2.5.8
     * @return string Description of the product
     */
    public function get_description()
    {
        return $this->post_content;
    }

    /**
     * Retrieve the base price
     *
     * @since 0.1
     * @return float Price of the product
     */
    public function get_base_price()
    {
        if (!isset($this->base_price)) {

            $this->base_price = get_post_meta($this->ID, '_smartpay_base_price', true);

            if ($this->base_price) {

                // TODO: Add sanitization
                $this->base_price = $this->base_price;
            } else {
                $this->base_price = '';
            }
        }

        // Override the product base price.
        return apply_filters('smartpay_get_product_base_price', $this->base_price, $this->ID);
    }

    /**
     * Retrieve the sale price
     *
     * @since 0.1
     * @return float Price of the product
     */
    public function get_sale_price()
    {
        if (!isset($this->sale_price)) {

            $this->sale_price = get_post_meta($this->ID, '_smartpay_sale_price', true);

            if ($this->sale_price) {

                // TODO: Add sanitization
                $this->sale_price = $this->sale_price;
            } else {
                $this->sale_price = '';
            }
        }

        // Override the product sale price.
        return apply_filters('smartpay_get_product_sale_price', $this->sale_price, $this->ID);
    }

    /**
     * Retrieve the variable variations
     *
     * @since 0.1
     * @return array List of the variable variations
     */
    public function get_variations()
    {
        $this->variations = array();

        if (true === $this->has_variations()) {

            if (empty($this->variations)) {
                $this->variations = get_post_meta($this->ID, '_smartpay_product_variations', true);
            }
        }

        // Override variations
        return apply_filters('smartpay_get_product_variations', $this->variations, $this->ID);
    }

    /**
     * Determine if the product has variations enabled
     *
     * @since 0.1
     * @return bool True when the product has variations, false otherwise
     */
    public function has_variations()
    {
        $ret = get_post_meta($this->ID, '_smartpay_has_variations', true);

        // Override whether the product has variables prices.
        return (bool) apply_filters('smartpay_has_variations', $ret, $this->ID);
    }

    /**
     * Retrieve the file products
     *
     * @since 0.1
     * @return array List of product files
     */
    public function get_files()
    {
        if (!isset($this->files)) {

            $this->files = array();

            $product_files = get_post_meta($this->ID, '_smartpay_product_files', true);

            if ($product_files) {
                $this->files = $product_files;
            }
        }

        return apply_filters('smartpay_product_files', $this->files, $this->ID);
    }

    /**
     * Retrieve product variation files
     *
     * @since 0.1
     * @param integer $variation_id
     * @return array List of product files
     */
    public function get_variation_files($variation_id = 1)
    {
        $variations = $this->get_variations();

        $variation_files = [];

        $variation_files_id = [];

        if (isset($variations[$variation_id])) {
            $variation_files_id = $variations[$variation_id]['files'];

            foreach ($variation_files_id as $file_id) {
                foreach ($this->get_files() as $product_file) {
                    if ($file_id == $product_file['id']) {
                        array_push($variation_files, $product_file);
                    }
                }
            }
        }

        return apply_filters('smartpay_product_variation_files', $variation_files, $this->ID, $variation_id);
    }

    /**
     * Retrieve the product notes
     *
     * @since 0.1
     * @return string Note related to the product
     */
    public function get_notes()
    {
        if (!isset($this->notes)) {

            $this->notes = get_post_meta($this->ID, '_smartpay_product_notes', true);
        }

        return (string) apply_filters('smartpay_product_notes', $this->notes, $this->ID);
    }

    /**
     * Retrieve the product sku
     *
     * @since 0.1
     * @return string SKU of the product
     */
    public function get_sku()
    {
        if (!isset($this->sku)) {

            $this->sku = get_post_meta($this->ID, '_smartpay_product_sku', true);

            if (empty($this->sku)) {
                $this->sku = '-';
            }
        }

        return apply_filters('smartpay_get_product_sku', $this->sku, $this->ID);
    }

    /**
     * Retrieve the sale count for the product
     *
     * @since 0.1
     * @return int Number of times this has been purchased
     */
    public function get_sales()
    {
        if (!isset($this->sales)) {

            if ('' == get_post_meta($this->ID, '_smartpay_product_sales', true)) {
                add_post_meta($this->ID, '_smartpay_product_sales', 0);
            }

            $this->sales = get_post_meta($this->ID, '_smartpay_product_sales', true);

            // Never let sales be less than zero
            $this->sales = max($this->sales, 0);
        }

        return $this->sales;
    }

    /**
     * Increment the sale count by one
     *
     * @since 0.1
     * @param int $quantity The quantity to increase the sales by
     * @return int New number of total sales
     */
    public function increase_sales($quantity = 1)
    {
        $quantity    = absint($quantity);
        $total_sales = $this->get_sales() + $quantity;

        if ($this->update_meta('_smartpay_product_sales', $total_sales)) {

            $this->sales = $total_sales;

            do_action('smartpay_product_increase_sales', $this->ID, $this->sales, $this);

            return $this->sales;
        }

        return false;
    }

    /**
     * Decrement the sale count by one
     *
     * @since 0.1
     * @param int $quantity The quantity to decrease by
     * @return int New number of total sales
     */
    public function decrease_sales($quantity = 1)
    {
        // Only decrease if not already zero
        if ($this->get_sales() > 0) {

            $quantity    = absint($quantity);
            $total_sales = $this->get_sales() - $quantity;

            if ($this->update_meta('_smartpay_product_sales', $total_sales)) {

                $this->sales = $total_sales;

                do_action('smartpay_product_decrease_sales', $this->ID, $this->sales, $this);

                return $this->sales;
            }
        }

        return false;
    }

    // TODO: Add earnings

    // TODO: is free

    // TODO: Can buy multiple

    /**
     * Updates a single meta entry for the product
     *
     * @since  2.3
     * @access private
     * @param  string $meta_key   The meta_key to update
     * @param  string|array|object $meta_value The value to put into the meta
     * @return bool             The result of the update query
     */
    private function update_meta($meta_key = '', $meta_value = '')
    {

        global $wpdb;

        if (empty($meta_key) || (!is_numeric($meta_value) && empty($meta_value))) {
            return false;
        }

        // Make sure if it needs to be serialized, we do
        $meta_value = maybe_serialize($meta_value);

        if (is_numeric($meta_value)) {
            $value_type = is_float($meta_value) ? '%f' : '%d';
        } else {
            $value_type = "'%s'";
        }

        $sql = $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key);

        if ($wpdb->query($sql)) {

            clean_post_cache($this->ID);
            return true;
        }

        return false;
    }

    /**
     * Checks if the product can be purchased
     *
     * @since  2.6.4
     * @return bool If the current user can purchase the product ID
     */
    public function can_purchase()
    {
        $can_purchase = true;

        if ($this->post_status != 'publish') {
            $can_purchase = false;
        }

        return (bool) apply_filters('smartpay_can_purchase_product', $can_purchase, $this);
    }
}