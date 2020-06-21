<?php

namespace SmartPay\Products;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Product_Variation
{
    /**
     * The variation ID
     *
     * @since  0.0.1
     * @var integer
     */
    public $ID = 0;
    protected $_ID = 0;

    /**
     * The variation name
     *
     * @since  0.0.1
     * @var string
     */
    protected $name = '';

    /**
     * The variation description
     *
     * @since  0.0.1
     * @var string
     */
    protected $description = '';

    /**
     * The variation parent
     *
     * @since  0.0.1
     * @var int
     */
    protected $parent = '';

    /**
     * The variation additional_amount
     *
     * @since  0.0.1
     * @var float
     */
    protected $additional_amount = 0;

    /**
     * The variation files
     *
     * @since  0.0.1
     * @var array
     */
    private $files = array();

    /**
     * The variation status
     *
     * @since  0.0.1
     * @var array
     */
    protected $status = 'publish';

    /**
     * The variation created_at time
     *
     * @since  0.0.1
     * @var integer
     */
    protected $created_at = '';
    /**
     * The variation updated_at time
     *
     * @since  0.0.1
     * @var integer
     */
    protected $updated_at = '';

    /**
     * The variation's sale count
     *
     * @since  0.0.1
     * @var integer
     */
    private $sales = 0;

    /**
     * The variation sku
     *
     * @since  0.0.1
     * @var string
     */
    protected $sku = '';

    /**
     * Identify if the variation is a new one or existing
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
        if (!$_id) return;

        $variation = \WP_Post::get_instance($_id);

        return $this->setup_variation($variation);
    }

    /**
     * Given the variation data, let's set the variables
     *
     * @since  0.0.1
     * @param  \WP_Post $variation
     * @return bool If the setup was successful or not
     */
    private function setup_variation($variation)
    {
        if (!is_object($variation)) {
            return false;
        }

        if (!$variation instanceof \WP_Post) {
            return false;
        }

        if ('sp_product_variation' !== $variation->post_type) {
            return false;
        }

        // Primary Identifier
        $this->ID = absint($variation->ID);

        // Protected ID that can never be changed
        $this->_ID = absint($variation->ID);

        $this->name              = $variation->post_title;
        $this->description       = $variation->post_content;
        $this->parent            = $variation->post_parent;
        $this->additional_amount = $this->setup_additional_amount();
        $this->files             = $this->setup_files() ?? [];
        $this->status            = $variation->post_status;
        $this->created_at        = $variation->post_date;
        $this->updated_at        = $variation->post_modified;
        $this->sales             = $this->setup_sales();
        $this->sku               = $this->setup_sku();

        return true;
    }

    /**
     * Get a post meta item for the variation
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

        $meta = apply_filters('smartpay_get_product_variation_meta_' . $meta_key, $meta, $this->ID);

        if (is_serialized($meta)) {
            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
            if (!empty($matches)) {
                $meta = array();
            }
        }

        return apply_filters('smartpay_get_product_variation_meta', $meta, $this->ID, $meta_key);
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

        $meta_value = apply_filters('smartpay_product_variation_update_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Setup the additional_amount
     *
     * @since  0.0.1
     * @return float variation additional amount
     */
    private function setup_additional_amount()
    {
        $additional_amount = (int) $this->get_meta('_product_variation_additional_amount');

        return $additional_amount;
    }

    /**
     * Setup the files
     *
     * @since  0.0.1
     * @return float The files associated with the variation
     */
    private function setup_files()
    {
        $files = $this->get_meta('_product_variation_files');
        if (!is_array($files)) $files = [];

        return $files;
    }

    /**
     * Setup the sales
     *
     * @since  0.0.1
     * @return float Total variation sales
     */
    private function setup_sales()
    {
        $sales = $this->get_meta('_product_variation_sales');
        return !empty($sales) ? $sales : 0;
    }

    /**
     * Setup the sku
     *
     * @since  0.0.1
     * @return float variation sku
     */
    private function setup_sku()
    {
        return $this->get_meta('_product_variation_sku');
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

            return new \WP_Error('smartpay-product-variation-invalid-property', sprintf(__('Can\'t get property %s', 'smartpay'), $key));
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
     * @return int ID of the variation
     */
    public function get_ID()
    {
        return $this->ID;
    }

    /**
     * Retrieve the name
     *
     * @since  0.0.1
     * @return string name of the variation
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * Retrieve the description
     *
     * @since  0.0.1
     * @return string description of the variation
     */
    public function get_description()
    {
        return $this->description;
    }

    /**
     * Retrieve the product parent
     *
     * @since  0.0.1
     * @return string parent of the product
     */
    public function get_parent()
    {
        return apply_filters('smartpay_product_variation_get_parent', $this->parent, $this->ID);
    }

    /**
     * Retrieve the additional_amount
     *
     * @since  0.0.1
     * @return string additional_amount of the product
     */
    public function get_additional_amount()
    {
        // Override the variation additional amount.
        return apply_filters('smartpay_product_variation_get_additional_amount', $this->additional_amount, $this->ID);
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
                add_post_meta($this->ID, '_product_variation_sales', 0);
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
     * @return int|bool False on failure, the variation ID on success.
     */
    private function insert()
    {
        if (0 != $this->ID) {
            return false;
        }
        // Create a blank variation
        $variation_id = wp_insert_post(array(
            'post_title'     => $this->name ?? '',
            'post_content'   => $this->description ?? '',
            'post_parent'   => $this->parent ?? 0,
            'post_type'      => 'sp_product_variation',
            'post_status'    => $this->status ?? 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if (!empty($variation_id)) {
            $this->ID   = $variation_id;
            $this->_ID  = $variation_id;

            $this->new  = true;
        }

        return $this->ID;
    }

    /**
     * One items have been set, an update is needed to save them to the database.
     *
     * @since  0.0.1
     * @return bool  True of the save occurred, false if it failed or wasn't needed
     */
    public function save()
    {
        $saved = false;

        if (!$this->ID) {
            $variation_id = $this->insert();

            if (false === $variation_id) {
                $saved = false;
            } else {
                $this->ID = $variation_id;
            }
        }

        if ($this->ID !== $this->_ID) {
            $this->ID = $this->_ID;
        }

        // If we have something pending, let's save it
        if (!empty($this->pending)) {
            foreach ($this->pending as $key => $value) {
                switch ($key) {
                    case 'name':
                        wp_update_post(array('ID' => $this->ID, 'post_title' => $this->name));
                        break;

                    case 'description':
                        wp_update_post(array('ID' => $this->ID, 'post_content' => $this->description));
                        break;

                    case 'additional_amount':
                        $this->update_meta('_product_variation_additional_amount', $this->additional_amount);
                        break;

                    case 'files':
                        $this->update_meta('_product_variation_files', $this->files);
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
            $this->setup_variation($this->ID);

            /** This action fires anytime that $variation->save() is run **/
            do_action('smartpay_product_variation_saved', $this->ID, $this);
        }

        return $saved;
    }

    /**
     * Delete the variation.
     *
     * @since  0.0.2
     * @return bool  True of the deleted, false if it failed
     */
    public function delete()
    {
        return wp_delete_post($this->_ID);
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

        if ($this->update_meta('_product_variation_sales', $total_sales)) {

            $this->sales = $total_sales;

            do_action('smartpay_product_variation_increase_sales', $this->ID, $this->sales, $this);

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

            if ($this->update_meta('_product_variation_sales', $total_sales)) {

                $this->sales = $total_sales;

                do_action('smartpay_product_variation_decrease_sales', $this->ID, $this->sales, $this);

                return $this->sales;
            }
        }

        return false;
    }

    /**
     * Checks if the product_variation can be purchased
     *
     * @since  0.0.1
     * @return bool If the current user can purchase the product_variation ID
     */
    public function can_purchase()
    {
        $can_purchase = true;

        if ($this->post_status != 'publish') {
            $can_purchase = false;
        }

        return (bool) apply_filters('smartpay_can_purchase_product_variation', $can_purchase, $this);
    }
}