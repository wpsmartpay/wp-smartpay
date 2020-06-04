<?php

namespace SmartPay\Products;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Product
{
    /**
     * The product ID
     *
     * @since  0.0.1
     * @var integer
     */
    public $ID = 0;
    protected $_ID = 0;

    /**
     * The product title
     *
     * @since  0.0.1
     * @var string
     */
    protected $title = '';

    /**
     * The product description
     *
     * @since  0.0.1
     * @var string
     */
    protected $description = '';

    /**
     * The product image
     *
     * @since  0.0.1
     * @var string
     */
    protected $image = '';

    /**
     * The product base price
     *
     * @since  0.0.1
     * @var float
     */
    protected $base_price = '';

    /**
     * The product sale price
     *
     * @since  0.0.1
     * @var float
     */
    protected $sale_price = '';

    /**
     * The product has variations
     *
     * @since  0.0.1
     * @var boolean
     */
    protected $has_variations = false;

    /**
     * The product variations
     *
     * @since  0.0.1
     * @var array
     */
    protected $variations = array();

    /**
     * The product files
     *
     * @since  0.0.1
     * @var array
     */
    private $files = array();

    /**
     * The product status
     *
     * @since  0.0.1
     * @var array
     */
    protected $status = 'publish';

    /**
     * The product created_at time
     *
     * @since  0.0.1
     * @var integer
     */
    protected $created_at = '';
    /**
     * The product updated_at time
     *
     * @since  0.0.1
     * @var integer
     */
    protected $updated_at = '';

    /**
     * The product's sale count
     *
     * @since  0.0.1
     * @var integer
     */
    private $sales = 0;

    /**
     * The product sku
     *
     * @since  0.0.1
     * @var string
     */
    protected $sku = '';

    /**
     * Identify if the product is a new one or existing
     *
     * @since  0.0.1
     * @var boolean
     */
    protected $new = false;

    /**
     * Array of items that have changed since the last save() was run
     * This is for internal use, to allow fewer update_post_meta calls to be run
     *
     * @since  0.0.1
     * @var array
     */
    private $pending = array();

    /**
     * Get things going
     *
     * @since  0.0.1
     */
    public function __construct($_id = false)
    {
        if (!$_id) {
            return;
        }

        $product = \WP_Post::get_instance($_id);

        return $this->setup_product($product);
    }

    /**
     * Given the product data, let's set the variables
     *
     * @since  0.0.1
     * @param  \WP_Post $product
     * @return bool If the setup was successful or not
     */
    private function setup_product($product)
    {
        if (!is_object($product)) {
            return false;
        }

        if (!$product instanceof \WP_Post) {
            return false;
        }

        if ('product' !== $product->post_type) {
            return false;
        }

        // Primary Identifier
        $this->ID = absint($product->ID);

        // Protected ID that can never be changed
        $this->_ID = absint($product->ID);

        $this->title          = $product->post_title;
        $this->description    = $product->post_content;
        $this->image          = $this->_setup_image();
        $this->base_price     = $this->_setup_base_price();
        $this->sale_price     = $this->_setup_sale_price();
        $this->has_variations = $this->has_variations();
        $this->variations     = $this->has_variations ? $this->_setup_variations() : [];
        $this->files          = $this->_setup_files() ?? [];
        $this->status         = $product->post_status;
        $this->created_at     = $product->post_date;
        $this->updated_at     = $product->post_modified;
        $this->sales          = $this->_setup_sales();
        $this->sku            = $this->_setup_sku();

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

        $meta_value = apply_filters('smartpay_product_update_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Setup the image
     *
     * @since  0.0.1
     * @return float Product image
     */
    private function _setup_image()
    {
        return has_post_thumbnail($this->ID) ? wp_get_attachment_url(get_post_thumbnail_id($this->ID), 'thumbnail') : '';
    }

    /**
     * Setup the base_price
     *
     * @since  0.0.1
     * @return float Product base price
     */
    private function _setup_base_price()
    {
        $base_price = $this->get_meta('_product_base_price');

        return !empty($base_price) ? (float) $base_price : null;
    }

    /**
     * Setup the sale_price
     *
     * @since  0.0.1
     * @return float Product sale price
     */
    private function _setup_sale_price()
    {
        $sale_price = (float) $this->get_meta('_product_sale_price');

        return !empty($sale_price) ? (float) $sale_price : null;
    }


    /**
     * Determine if the product has variations enabled
     *
     * @since  0.0.1
     * @return bool True when the product has variations, false otherwise
     */
    public function has_variations()
    {
        $has_variation = $this->get_meta('_product_has_variations');

        // Override whether the product has variables prices.
        return (bool) apply_filters('smartpay_product_has_variations', $has_variation, $this->ID);
    }

    /**
     * Setup the variations
     *
     * @since  0.0.1
     * @return float The variations associated with the product
     */
    private function _setup_variations()
    {
        $the_query = new \WP_Query(array(
            'post_parent' => $this->ID,
            'post_type' => 'product_variation',
        ));

        $child_products = $the_query->have_posts() ? $the_query->get_posts() : [];

        wp_reset_postdata();

        $variations = [];
        foreach ($child_products as $product) {

            $variation = new Product_Variation($product->ID);

            array_push($variations, array(
                'id' => $variation->ID,
                'name' => $variation->name,
                'additional_amount' => $variation->additional_amount,
                'description' => $variation->description,
                'files' => $variation->files,
            ));
        }

        usort($variations, function ($a, $b) {
            return $a['id'] <=> $b['id'];
        });

        return $variations;
    }

    /**
     * Setup the files
     *
     * @since  0.0.1
     * @return float The files associated with the product
     */
    private function _setup_files()
    {
        $files = $this->get_meta('_product_files');
        if (!is_array($files)) $files = [];

        return $files;
    }

    /**
     * Setup the sales
     *
     * @since  0.0.1
     * @return float Total product sales
     */
    private function _setup_sales()
    {
        $sales = $this->get_meta('_product_sales');
        return !empty($sales) ? $sales : 0;
    }

    /**
     * Setup the sku
     *
     * @since  0.0.1
     * @return float Product sku
     */
    private function _setup_sku()
    {
        return $this->get_meta('_product_sku');
    }

    /**
     * Magic __get function to dispatch a call to retrieve a private property
     *
     * @since  0.0.1
     */
    public function __get($key)
    {
        if (method_exists($this, 'get_' . $key)) {

            return call_user_func(array($this, 'get_' . $key));
        } else {

            return new \WP_Error('smartpay-product-invalid-property', sprintf(__('Can\'t get property %s', 'smartpay'), $key));
        }
    }

    /**
     * Magic SET function
     *
     * Sets up the pending array for the save method
     *
     * @since  0.0.1
     * @param string $key   The property name
     * @param mixed $value  The value of the property
     */
    public function __set($key, $value)
    {
        if (!in_array($key, ['_ID'])) {
            $this->pending[$key] = $value;
            $this->$key = $value;
        }
    }

    /**
     * Retrieve the ID
     *
     * @since  0.0.1
     * @return int ID of the product
     */
    public function get_ID()
    {
        return $this->ID;
    }

    /**
     * Retrieve the title
     *
     * @since  0.0.1
     * @return string title of the product
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * Retrieve the description
     *
     * @since  0.0.1
     * @return string description of the product
     */
    public function get_description()
    {
        return $this->description;
    }

    /**
     * Retrieve the image
     *
     * @since  0.0.1
     * @return string image of the product
     */
    public function get_image()
    {
        // Override the product base price.
        return apply_filters('smartpay_product_get_image', $this->image, $this->ID);
    }

    /**
     * Retrieve the base_price
     *
     * @since  0.0.1
     * @return string base_price of the product
     */
    public function get_base_price()
    {
        // Override the product base price.
        return apply_filters('smartpay_product_get_base_price', $this->base_price, $this->ID);
    }

    /**
     * Retrieve the sale_price
     *
     * @since  0.0.1
     * @return string sale_price of the product
     */
    public function get_sale_price()
    {
        // Override the product base price.
        return apply_filters('smartpay_product_get_sale_price', $this->sale_price, $this->ID);
    }

    /**
     * Retrieve the variations
     *
     * @since  0.0.1
     * @return string variations of the product
     */
    public function get_variations()
    {
        return apply_filters('smartpay_product_get_variations', $this->variations, $this->ID);
    }

    /**
     * Retrieve the files
     *
     * @since  0.0.1
     * @return string files of the product
     */
    public function get_files()
    {
        return apply_filters('smartpay_product_get_files', $this->files, $this->ID);
    }

    /**
     * Retrieve the product status
     *
     * @since  0.0.1
     * @return string Status of the product
     */
    public function get_status()
    {
        return apply_filters('smartpay_product_get_status', $this->status, $this->ID);
    }

    /**
     * Retrieve the product sales
     *
     * @since  0.0.1
     * @return string sales of the product
     */
    public function get_sales()
    {
        if (!isset($this->sales)) {

            if (!$this->sales) {
                add_post_meta($this->ID, '_product_sales', 0);
            }
        }

        // Never let sales be less than zero
        $this->sales = max($this->sales, 0);

        return apply_filters('smartpay_product_get_sales', $this->files, $this->ID);;
    }

    /**
     * Retrieve the product sku
     *
     * @since  0.0.1
     * @return string sku of the product
     */
    public function get_sku()
    {
        if (empty($this->sku)) {
            $this->sku = '-';
        }

        return apply_filters('smartpay_product_get_sku', $this->sku, $this->ID);
    }

    /**
     * Create the base of a product.
     *
     * @since  0.0.1
     * @return int|bool False on failure, the product ID on success.
     */
    private function _insert()
    {
        if (0 != $this->ID) {
            return false;
        }
        var_dump($this);
        exit;
        // Create a blank product
        $product_id = wp_insert_post(array(
            'post_title'     => $this->title ?? '',
            'post_content'   => $this->description ?? '',
            'post_type'      => 'product',
            'post_status'    => $this->status ?? 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if (!empty($product_id)) {
            $this->ID   = $product_id;
            $this->_ID  = $product_id;

            $this->new  = true;
        }

        return $this->ID;
    }

    /**
     * One items have been set, an update is needed to save them to the database.
     *
     * @return bool  True of the save occurred, false if it failed or wasn't needed
     */
    public function save()
    {
        $saved = false;

        if (!$this->ID) {
            $product_id = $this->_insert();

            if (false === $product_id) {
                $saved = false;
            } else {
                $this->ID = $product_id;
            }
        }

        if ($this->ID !== $this->_ID) {
            $this->ID = $this->_ID;
        }

        // If we have something pending, let's save it
        if (!empty($this->pending)) {
            foreach ($this->pending as $key => $value) {
                switch ($key) {
                    case 'title':
                        wp_update_post(array('ID' => $this->ID, 'post_title' => $this->title));
                        break;

                    case 'description':
                        wp_update_post(array('ID' => $this->ID, 'post_content' => $this->description));
                        break;

                    case 'base_price':
                        $this->update_meta('_product_base_price', $this->base_price);
                        break;

                    case 'sale_price':
                        $this->update_meta('_product_sale_price', $this->sale_price);
                        break;

                    case 'has_variations':
                        $this->update_meta('_product_has_variations', $this->has_variations);
                        break;

                    case 'files':
                        $this->update_meta('_product_files', $this->files);
                        break;

                    case 'status':
                        wp_update_post(array('ID' => $this->ID, 'post_status' => $this->status));
                        break;

                    case 'created_at':
                        wp_update_post(array('ID' => $this->ID, 'post_date' => $this->created_at));
                        break;

                    case 'updated_at':
                        wp_update_post(array('ID' => $this->ID, 'post_modified' => $this->updated_at));
                        break;

                    default:
                        /** Used to save non-standard data. Developers can hook here if they want to save **/
                        do_action('smartpay_product_data_save', $this, $key);
                        break;
                }
            }

            $this->pending = array();
            $saved         = true;
        }

        if (true === $saved) {
            $this->setup_product($this->ID);

            /** This action fires anytime that $product->save() is run **/
            do_action('smartpay_product_saved', $this->ID, $this);
        }

        return $saved;
    }

    /**
     * Increment the sale count by one
     *
     * @since  0.0.1
     * @param int $quantity The quantity to increase the sales by
     * @return int New number of total sales
     */
    public function increase_sales($quantity = 1)
    {
        $quantity    = absint($quantity);
        $total_sales = $this->get_sales() + $quantity;

        if ($this->update_meta('_product_sales', $total_sales)) {

            $this->sales = $total_sales;

            do_action('smartpay_product_increase_sales', $this->ID, $this->sales, $this);

            return $this->sales;
        }

        return false;
    }

    /**
     * Decrement the sale count by one
     *
     * @since  0.0.1
     * @param int $quantity The quantity to decrease by
     * @return int New number of total sales
     */
    public function decrease_sales($quantity = 1)
    {
        // Only decrease if not already zero
        if ($this->get_sales() > 0) {

            $quantity    = absint($quantity);
            $total_sales = $this->get_sales() - $quantity;

            if ($this->update_meta('_product_sales', $total_sales)) {

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

        if ($this->status != 'publish') {
            $can_purchase = false;
        }

        return (bool) apply_filters('smartpay_can_purchase_product', $can_purchase, $this);
    }

    // TODO: Add earnings

    // TODO: is free

    // TODO: Can buy multiple
}