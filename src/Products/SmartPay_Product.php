<?php

namespace SmartPay\Products;

use WP_Error;
use WP_Post;
use WP_Query;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Product
{
    /**
     * The product ID
     *
     * @since 0.0.1
     * @var integer
     */
    public $ID = 0;
    protected $_ID = 0;

    /**
     * The product type (default: product)
     *
     * @since  0.0.1
     * @var string
     */
    public $type = 'product';

    /**
     * The product base price
     *
     * @since 0.0.1
     * @var float
     */
    protected $base_price;

    /**
     * The product sale price
     *
     * @since 0.0.1
     * @var float
     */
    protected $sale_price;

    /**
     * The product has variations
     *
     * @since 0.0.1
     * @var boolean
     */
    public $has_variations = false;

    /**
     * The product variations
     *
     * @since 0.0.1
     * @var array
     */
    protected $variations = array();

    /**
     * The product files
     *
     * @since 0.0.1
     * @var array
     */
    private $files = array();

    /**
     * The product status
     *
     * @since 0.0.1
     * @var array
     */
    public $status = 'publish';

    /**
     * The parent product (if applicable)
     *
     * @since  0.0.1
     * @var integer
     */
    protected $parent = 0;

    /**
     * The product additional amount
     *
     * @since 0.0.1
     * @var float
     */
    protected $additional_amount = 0;

    /**
     * The product created_at time
     *
     * @since  0.0.1
     * @var integer
     */
    public $created_at = '';
    /**
     * The product updated_at time
     *
     * @since  0.0.1
     * @var integer
     */
    public $updated_at = '';

    /**
     * The product's sale count
     *
     * @since 0.0.1
     * @var integer
     */
    private $sales;

    /**
     * The product sku
     *
     * @since 0.0.1
     * @var string
     */
    public $sku;

    /**
     * Identify if the product is a new one or existing
     *
     * @since  0.0.1
     * @var boolean
     */
    protected $new = false;

    /**
     * Array of items that have changed since the last save() was run
     * This is for internal use, to allow fewer update_product_meta calls to be run
     *
     * @since  0.0.1
     * @var array
     */
    private $pending = array();

    /** The default properties in WP_Post **/
    // private $post_date = '0000-00-00 00:00:00';
    // private $post_date_gmt = '0000-00-00 00:00:00';
    // private $post_content = '';
    // private $post_title = '';
    // private $post_status = 'publish';
    // private $comment_status = 'closed';
    // private $ping_status = 'closed';
    // private $post_name = '';
    // private $post_modified = '0000-00-00 00:00:00';
    // private $post_modified_gmt = '0000-00-00 00:00:00';
    // private $post_content_filtered = '';
    // private $post_parent = 0;

    /**
     * Get things going
     *
     * @since 0.0.1
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
     * @since  0.0.1
     * @param  WP_Post $product
     * @return bool If the setup was successful or not
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

        // Primary Identifier
        $this->ID = absint($product->ID);

        // Protected ID that can never be changed
        $this->_ID = absint($product->ID);

        $this->type              = $this->setup_type();
        $this->title             = $product->post_title;
        $this->description       = $product->post_content;
        $this->base_price        = $this->setup_base_price();
        $this->sale_price        = $this->setup_sale_price();
        $this->has_variations    = $this->has_variations();
        $this->variations        = $this->has_variations ? $this->setup_variations() : [];
        $this->files             = $this->setup_files() ?? [];
        $this->status            = $product->post_status;
        $this->parent            = 'variation' == $this->type ? $product->post_parent : 0;
        $this->additional_amount = 'variation' == $this->type ? $this->setup_additional_amount() : 0;
        $this->created_at        = $product->post_date;
        $this->updated_at        = $product->post_modified;
        $this->sales             = $this->setup_sales();
        $this->sku               = $this->setup_sku();

        return true;
    }

    /**
     * Get a post meta item for the product
     *
     * @since  0.0.1
     * @param  string   $meta_key The Meta Key
     * @param  boolean  $single   Return single item or array
     * @return mixed    The value from the post meta
     */
    public function get_meta($meta_key = '', $single = true)
    {
        if (empty($meta_key)) {
            return;
        }

        $meta = get_post_meta($this->ID, $meta_key, $single);

        $meta = apply_filters('smartpay_get_product_meta_' . $meta_key, $meta, $this->ID);

        if (is_serialized($meta)) {
            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
            if (!empty($matches)) {
                $meta = array();
            }
        }

        return apply_filters('smartpay_get_product_meta', $meta, $this->ID, $meta_key);
    }

    /**
     * Update the post meta
     *
     * @since  0.0.1
     * @param  string $meta_key   The meta key to update
     * @param  string $meta_value The meta value
     * @param  string $prev_value Previous meta value
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function update_meta($meta_key = '', $meta_value = '', $prev_value = '')
    {
        if (empty($meta_key)) {
            return;
        }

        $meta_value = apply_filters('smartpay_update_product_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Setup the base_price
     *
     * @since  0.0.1
     * @return float Product base price
     */
    private function setup_base_price()
    {
        $base_price = $this->get_meta('_smartpay_base_price', true);

        if ('variation' == $this->type) {
            $base_price += $this->setup_additional_amount();
        }

        return apply_filters('smartpay_get_product_base_price' . $base_price, $this->ID);
    }

    /**
     * Setup the sale_price
     *
     * @since  0.0.1
     * @return float Product sale price
     */
    private function setup_sale_price()
    {
        $sale_price = $this->get_meta('_smartpay_sale_price', true);

        if ('variation' == $this->type) {
            $sale_price += $this->setup_additional_amount();
        }

        return apply_filters('smartpay_get_product_sale_price' . $sale_price, $this->ID);
    }

    /**
     * Setup the variations
     *
     * @since  0.0.1
     * @return float The variations associated with the product
     */
    private function setup_variations()
    {
        // TODO: Set variations
        //has_variations()
        return [];
    }

    /**
     * Setup the files
     *
     * @since  0.0.1
     * @return float The files associated with the product
     */
    private function setup_files()
    {
        return $this->get_meta('_smartpay_files', true);
    }

    /**
     * Setup the product type
     *
     * @since  0.0.1
     * @return string Product type
     */
    private function setup_type()
    {
        return $this->get_meta('_smartpay_type', true);
    }

    /**
     * Setup the variations additional_amount
     *
     * @since  0.0.1
     * @return float Total product additional_amount
     */
    private function setup_additional_amount()
    {
        return $this->get_meta('_smartpay_additional_amount', true);
    }

    /**
     * Setup the sales
     *
     * @since  0.0.1
     * @return float Total product sales
     */
    private function setup_sales()
    {
        return $this->get_meta('_smartpay_sales', true);
    }

    /**
     * Setup the sku
     *
     * @since  0.0.1
     * @return float Product sku
     */
    private function setup_sku()
    {
        return $this->get_meta('_smartpay_sku', true);
    }

    /**
     * Magic __get function to dispatch a call to retrieve a private property
     *
     * @since 0.0.1
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
     * Retrieve the ID
     *
     * @since 0.0.1
     * @return int ID of the product
     */
    public function get_ID()
    {
        return $this->ID;
    }

    /**
     * Retrieve the title
     *
     * @since 0.0.1
     * @return string title of the product
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * Retrieve the description
     *
     * @since 0.0.1
     * @return string description of the product
     */
    public function get_description()
    {
        return $this->description;
    }

    /**
     * Retrieve the base_price
     *
     * @since 0.0.1
     * @return string base_price of the product
     */
    public function get_base_price()
    {
        // Override the product base price.
        return apply_filters('smartpay_get_product_base_price', $this->base_price, $this->ID);
    }

    /**
     * Retrieve the sale_price
     *
     * @since 0.0.1
     * @return string sale_price of the product
     */
    public function get_sale_price()
    {
        // Override the product base price.
        return apply_filters('smartpay_get_product_sale_price', $this->sale_price, $this->ID);
    }

    /**
     * Retrieve the variations
     *
     * @since 0.0.1
     * @return string variations of the product
     */
    public function get_variations()
    {
        $this->variations = array();

        if (true === $this->has_variations()) {
            $the_query = new WP_Query(array(
                'post_parent' => $this->ID,
                'post_type' => 'smartpay_product',
            ));

            $this->variations = $the_query->have_posts();

            wp_reset_postdata();
        }

        // Override variations
        return apply_filters('smartpay_get_product_variations', $this->variations, $this->ID);
    }

    /**
     * Retrieve the files
     *
     * @since 0.0.1
     * @return string files of the product
     */
    public function get_files()
    {
        return apply_filters('smartpay_get_product_files', $this->files, $this->ID);
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
     * Retrieve the product type
     *
     * @since 2.5.8
     * @return string type of the product
     */
    public function get_type()
    {
        return $this->type ?? 'product';
    }

    /**
     * Retrieve the product variation additional_amount
     *
     * @since 2.5.8
     * @return string additional_amount of the product variation
     */
    public function get_additional_amount()
    {
        $amount = 0;

        if ('variation' == $this->get_type()) {
            $amount = $this->additional_amount;
        }

        return apply_filters('smartpay_get_product_variation_additional_amount', $amount, $this->ID);
    }

    /**
     * Retrieve the product parent
     *
     * @since 2.5.8
     * @return string parent of the product
     */
    public function get_parent()
    {
        return $this->parent ?? 0;
    }
    /**
     * Retrieve the product sales
     *
     * @since 2.5.8
     * @return string sales of the product
     */
    public function get_sales()
    {
        if (!isset($this->sales)) {

            if (!$this->sales) {
                add_post_meta($this->ID, '_smartpay_sales', 0);
            }
        }

        // Never let sales be less than zero
        $this->sales = max($this->sales, 0);

        return apply_filters('smartpay_get_product_sales', $this->files, $this->ID);;
    }

    /**
     * Retrieve the product sku
     *
     * @since 2.5.8
     * @return string sku of the product
     */
    public function get_sku()
    {
        if (empty($this->sku)) {
            $this->sku = '-';
        }

        return apply_filters('smartpay_get_product_sku', $this->sku, $this->ID);
    }

    /**
     * Creates a product variation
     *
     * @since  0.0.1
     * @param  array  $data Array of attributes for a product variation
     * @return mixed  false if data isn't passed and class not instantiated for creation, or New product ID
     */
    public function create_variation($data = array())
    {
        if (0 != $this->ID) {
            return false;
        }

        $args = wp_parse_args(
            $data,
            array(
                'post_type'   => 'smartpay_product',
                'post_parent'    => $this->ID,
                'post_status' => $this->status,
                'post_title'  => __($this->title . ' - ' . 'variation', 'smartpay'),
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            )
        );

        // Create a blank payment
        $product_id = wp_insert_post($args);

        $product = WP_Post::get_instance($product_id);

        return $this->setup_product($product);
    }

    /**
     * Determine if the product has variations enabled
     *
     * @since 0.0.1
     * @return bool True when the product has variations, false otherwise
     */
    public function has_variations()
    {
        $has_variation = false;

        if ('product' == $this->get_type()) {
            $has_variation = get_post_meta($this->ID, '_smartpay_has_variations', true);
        }

        // Override whether the product has variables prices.
        return (bool) apply_filters('smartpay_has_variations', $has_variation, $this->ID);
    }

    /**
     * Increment the sale count by one
     *
     * @since 0.0.1
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
     * @since 0.0.1
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

    /**
     * Checks if the product can be purchased
     *
     * @since  0.0.1
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

    // TODO: Add earnings

    // TODO: is free

    // TODO: Can buy multiple
}