<?php

namespace SmartPay\Customers;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Customer
{
    /**
     * The customer ID
     *
     * @since  0.0.1
     * @var    integer
     */
    public    $ID  = 0;
    protected $_ID = 0;

    /**
     * customer type
     *
     * @since  0.0.1
     * @var string
     */
    protected $purchase_type = '';

    /**
     * Purchase data
     *
     * @since  0.0.1
     * @var array
     */
    protected $purchase_data = [];

    /**
     * The date the customer was created
     *
     * @since  0.0.1
     * @var string
     */
    protected $date = '';

    /**
     * The date the customer was marked as 'complete'
     *
     * @since  0.0.1
     * @var string
     */
    protected $completed_date = '';

    /**
     * The status of the customer
     *
     * @since  0.0.1
     * @var string
     */
    protected $status      = 'pending';
    protected $post_status = 'pending'; // Same as $status but here for backwards compat

    /**
     * The display name of the current customer status
     *
     * @since  0.0.1
     * @var string
     */
    protected $status_nicename = '';

    /**
     * The total amount the customer
     *
     * @since  0.0.1
     * @var float
     */
    protected $amount = 0.00;

    /**
     * The the customer currency
     *
     * @since  0.0.1
     * @var string
     */
    protected $currency = '';

    /**
     * The customer gateway
     *
     * @since  0.0.1
     * @var string
     */
    protected $gateway = '';

    /**
     * The transaction ID returned by the gateway
     *
     * @since  0.0.1
     * @var string
     */
    protected $transaction_id = '';

    /**
     * The customer user info
     *
     * @since  0.0.1
     * @var array
     */
    protected $customer = [];

    /**
     * The email used for the customer
     *
     * @since  0.0.1
     * @var string
     */
    protected $email = '';

    /**
     * The Unique customer Key
     *
     * @since  0.0.1
     * @var string
     */
    protected $key = '';

    /**
     * The parent customer (if applicable)
     *
     * @since  0.0.1
     * @var integer
     */
    protected $parent_customer = 0;

    /**
     * The Gateway mode the customer was made in
     *
     * @since  0.0.1
     * @var string
     */
    protected $mode = 'live';

    /**
     * Identify if the customer is a new one or existing
     *
     * @since  0.0.1
     * @var boolean
     */
    protected $new = false;

    /**
     * When updating, the old status prior to the change
     *
     * @since  0.0.1
     * @var string
     */
    protected $old_status = '';

    /**
     * Array of items that have changed since the last save() was run
     * This is for internal use, to allow fewer update_customer_meta calls to be run
     *
     * @since  0.0.1
     * @var array
     */
    private $pending = [];

    /**
     * Setup the smartpay customers class
     *
     * @since 0.0.1
     * @param int $customer_id A given customer
     * @return mixed void|false
     */
    public function __construct($_id_or_email = false, $by_user_id = true)
    {

        $this->db = new DB_Customer;

        if (false === $_id_or_email || (is_numeric($_id_or_email) && (int) $_id_or_email !== absint($_id_or_email))) {
            return false;
        }

        $by_user_id = is_bool($by_user_id) ? $by_user_id : false;

        if (is_numeric($_id_or_email)) {
            $field = $by_user_id ? 'user_id' : 'id';
        } else {
            $field = 'email';
        }

        $customer = $this->db->get_customer_by($field, $_id_or_email);

        if (empty($customer) || !is_object($customer)) {
            return false;
        }

        $this->setup_customer($customer);
    }

    /**
     * Magic GET function
     *
     * @since  0.0.1
     * @param  string $key  The property
     * @return mixed        The value
     */
    public function __get($key)
    {
        if (method_exists($this, 'get_' . $key)) {

            $value = call_user_func(array($this, 'get_' . $key));
        } else {

            $value = $this->$key ?? '';
        }

        return $value;
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
        $ignore = array('_ID');

        if ($key === 'status') {
            $this->old_status = $this->status;
        }

        if (!in_array($key, $ignore)) {
            $this->pending[$key] = $value;
        }

        if ('_ID' !== $key) {
            $this->$key = $value;
        }
    }

    /**
     * Setup customer properties
     *
     * @since  0.0.1
     * @param  int  $customer_id The customer ID
     * @return bool If the setup was successful or not
     */
    private function setup_customer($customer_id)
    {
        if (empty($customer_id)) {
            return false;
        }

        $customer = get_post($customer_id);
        if (!$customer || is_wp_error($customer)) {
            return false;
        }

        if ('smartpay_customer' !== $customer->post_type) {
            return false;
        }

        // Primary Identifier
        $this->ID               = absint($customer_id);

        // Protected ID that can never be changed
        $this->_ID              = absint($customer_id);

        $this->purchase_type    = $this->setup_purchase_type();
        $this->purchase_data    = $this->setup_purchase_data();

        // Status and Dates
        $this->date             = $customer->post_date;
        $this->completed_date   = $this->setup_completed_date();
        $this->status           = $customer->post_status;
        $all_customer_statuses   = smartpay_get_customer_statuses();
        $this->status_nicename  = array_key_exists($this->status, $all_customer_statuses) ? $all_customer_statuses[$this->status] : ucfirst($this->status);

        $this->amount           = $this->setup_amount();
        $this->currency         = $this->setup_currency();
        $this->gateway  = $this->setup_gateway();
        $this->transaction_id   = $this->setup_transaction_id();

        $this->customer         = $this->setup_customer();

        $this->email            = $this->setup_email();

        // Other Identifiers
        $this->key              = $this->setup_customer_key();

        $this->parent_customer   = $customer->post_parent;

        $this->mode             = $this->setup_mode();

        return true;
    }

    /**
     * One items have been set, an update is needed to save them to the database.
     *
     * @return bool  True of the save occurred, false if it failed or wasn't needed
     */
    public function save()
    {
        $saved = false;

        if (empty($this->ID)) {

            $customer_id = $this->insert_customer();

            if (false === $customer_id) {
                $saved = false;
            } else {
                $this->ID = $customer_id;
            }
        }

        if ($this->ID !== $this->_ID) {
            $this->ID = $this->_ID;
        }

        // If we have something pending, let's save it
        if (!empty($this->pending)) {

            foreach ($this->pending as $key => $value) {
                switch ($key) {
                    case 'purchase_type':
                        $this->update_meta('_customer_purchase_type', $this->purchase_type);
                        break;

                    case 'purchase_data':
                        $this->update_meta('_customer_purchase_data', $this->purchase_data);
                        break;

                    case 'date':
                        $args = array(
                            'ID'        => $this->ID,
                            'post_date' => $this->date,
                            'edit_date' => true,
                        );

                        wp_update_post($args);
                        break;

                    case 'completed_date':
                        $this->update_meta('_customer_completed_date', $this->completed_date);
                        break;

                    case 'status':
                        $this->update_status($this->status);
                        break;

                    case 'amount':
                        $this->update_meta('_customer_amount', $this->amount);
                        break;

                    case 'currency':
                        $this->update_meta('_customer_currency', $this->currency);
                        break;

                    case 'gateway':
                        $this->update_meta('_customer_gateway', $this->gateway);
                        break;

                    case 'transaction_id':
                        $this->update_meta('_customer_transaction_id', $this->transaction_id);
                        break;

                    case 'customer':
                        $this->update_meta('_customer_customer_data', $this->customer);
                        break;

                    case 'email':
                        $this->update_meta('_customer_email', $this->email);
                        break;

                    case 'key':
                        $this->update_meta('_customer_key', $this->key);
                        break;

                    case 'parent_customer':
                        $args = array(
                            'ID'          => $this->ID,
                            'post_parent' => $this->parent_customer,
                        );

                        wp_update_post($args);
                        break;

                    case 'mode':
                        $this->update_meta('_customer_mode', $this->mode);
                        break;

                    default:
                        /**
                         * Used to save non-standard data. Developers can hook here if they want to save
                         * specific customer data when $customer->save() is run and their item is in the $pending array
                         */
                        do_action('smartpay_customer_save', $this, $key);
                        break;
                }
            }

            $this->pending = array();
            $saved         = true;
        }

        if (true === $saved) {
            $this->setup_customer($this->ID);

            /**
             * This action fires anytime that $customer->save() is run, allowing developers to run actions
             * when a customer is updated
             */
            do_action('customer_data_customer_saved', $this->ID, $this);
        }

        /**
         * Update the customer in the object cache
         */
        // $cache_key = md5('customer_data_customer' . $this->ID);
        // wp_cache_set($cache_key, $this, 'customers');

        return $saved;
    }

    /**
     * Create the base of a customer.
     *
     * @since  0.0.1
     * @return int|bool False on failure, the customer ID on success.
     */
    private function insert_customer()
    {
        // Unique key
        $this->key = strtolower(md5($this->email . date('Y-m-d H:i:s') . rand(1, 10)));
        $this->pending['key'] = $this->key;

        // Create a blank customer
        $customer_id = wp_insert_post(array(
            'post_type'      => 'smartpay_customer',
            'post_status'    => $this->status ?? 'pending',
            'post_date'      => !empty($this->date) ? $this->date : null,
            'post_date_gmt'  => !empty($this->date) ? get_gmt_from_date($this->date) : null,
            'post_parent'    => $this->parent_customer,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if (!empty($customer_id)) {
            $this->ID   = $customer_id;
            $this->_ID  = $customer_id;

            $this->new  = true;
        }

        return $this->ID;
    }

    /**
     * Set the customer status and run any status specific changes necessary
     *
     * @since 0.0.1
     *
     * @param  string $status The status to set the customer to
     * @return bool Returns if the status was successfully updated
     */
    public function update_status($status = false)
    {
        if ($status == 'completed' || $status == 'complete') {
            $status = 'publish';
        }

        $old_status = !empty($this->old_status) ? $this->old_status : false;

        if ($old_status === $status) {
            return false; // Don't permit status changes that aren't changes
        }

        $updated = false;

        do_action('smartpay_before_customer_status_change', $this->ID, $status, $old_status);

        $update_fields = array('ID' => $this->ID, 'post_status' => $status, 'edit_date' => current_time('mysql'));

        $updated = wp_update_post(apply_filters('smartpay_update_customer_status_fields', $update_fields));

        $this->status = $status;
        $this->post_status = $status;

        $all_customer_statuses  = smartpay_get_customer_statuses();
        $this->status_nicename = array_key_exists($status, $all_customer_statuses) ? $all_customer_statuses[$status] : ucfirst($status);

        // Process any specific status functions
        // switch ($status) {
        //     case 'refunded':
        //         $this->process_refund();
        //         break;
        //     case 'failed':
        //         $this->process_failure();
        //         break;
        //     case 'pending' || 'processing':
        //         $this->process_pending();
        //         break;
        // }

        do_action('smartpay_update_customer_status', $this->ID, $status, $old_status);

        return $updated;
    }

    /**
     * Get a post meta item for the customer
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

        $meta = apply_filters('smartpay_get_customer_meta_' . $meta_key, $meta, $this->ID);

        if (is_serialized($meta)) {
            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
            if (!empty($matches)) {
                $meta = array();
            }
        }

        return apply_filters('smartpay_get_customer_meta', $meta, $this->ID, $meta_key);
    }

    /**
     * Update the post meta
     *
     * @since  0.0.1
     * @param  string $meta_key   The meta key to update
     * @param  string $meta_value The meta value
     * @param  string $prev_value Previous meta value
     * @return int|bool           Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function update_meta($meta_key = '', $meta_value = '', $prev_value = '')
    {
        if (empty($meta_key)) {
            return;
        }

        $meta_value = apply_filters('smartpay_update_customer_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Add an item to the customer meta
     *
     * @since 2.8
     * @param string $meta_key
     * @param string $meta_value
     * @param bool   $unique
     *
     * @return bool|false|int
     */
    public function add_meta($meta_key = '', $meta_value = '', $unique = false)
    {
        if (empty($meta_key)) {
            return false;
        }

        return add_post_meta($this->ID, $meta_key, $meta_value, $unique);
    }

    /**
     * Delete an item from customer meta
     *
     * @since 2.8
     * @param string $meta_key
     * @param string $meta_value
     *
     * @return bool
     */
    public function delete_meta($meta_key = '', $meta_value = '')
    {
        if (empty($meta_key)) {
            return false;
        }

        return delete_post_meta($this->ID, $meta_key, $meta_value);
    }

    /**
     * Setup the user info
     *
     * @since  0.0.1
     * @return array The user info associated with the customer
     */
    private function setup_purchase_type()
    {
        return $this->get_meta('_smartpay_purchase_type', true);
    }

    /**
     * Setup the user info
     *
     * @since  0.0.1
     * @return array The user info associated with the customer
     */
    private function setup_purchase_data()
    {
        return $this->get_meta('_smartpay_purchase_data', true);
    }

    /**
     * Setup the customer completed date
     *
     * @since  0.0.1
     * @return string The date the customer was completed
     */
    private function setup_completed_date()
    {
        $customer = get_post($this->ID);

        if ('pending' == $customer->post_status || 'preapproved' == $customer->post_status || 'processing' == $customer->post_status) {
            return false; // This customer was never completed
        }

        $date = ($date = $this->get_meta('_customer_completed_date', true)) ? $date : $customer->date;

        return $date;
    }

    /**
     * Setup the customer amount
     *
     * @since  0.0.1
     * @return float The customer amount
     */
    private function setup_amount()
    {
        return $this->get_meta('_customer_amount', true);
    }

    /**
     * Setup the currency code
     *
     * @since  0.0.1
     * @return string The currency for the customer
     */
    private function setup_currency()
    {
        return $this->get_meta('_customer_currency', true) ?? smartpay_get_currency();
    }

    /**
     * Setup the customer gateway
     *
     * @since  0.0.1
     * @return string The customer gateway
     */
    private function setup_gateway()
    {
        return $this->get_meta('_customer_gateway');
    }

    /**
     * Setup the transaction ID
     *
     * @since  0.0.1
     * @return string The transaction ID for the customer
     */
    private function setup_transaction_id()
    {
        $transaction_id = $this->get_meta('_customer_transaction_id', true);

        if (empty($transaction_id) || (int) $transaction_id === (int) $this->ID) {

            $gateway        = $this->gateway;
            $transaction_id = apply_filters('smartpay_get_customer_transaction_id-' . $gateway, $this->ID);
        }

        return $transaction_id;
    }

    /**
     * Setup the email address for the purchase
     *
     * @since  0.0.1
     * @return string The email address for the customer
     */
    private function setup_email()
    {
        return  $this->get_meta('_customer_email', true);
    }

    public function attach_payment($payment_id = 0, $update_stats = true)
    {
        //
    }

    public function remove_payment($payment_id = 0, $update_stats = true)
    {
        //
    }
}