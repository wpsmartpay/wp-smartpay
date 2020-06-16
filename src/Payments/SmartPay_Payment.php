<?php

namespace SmartPay\Payments;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Payment
{
    /**
     * The Payment ID
     *
     * @since  0.0.1
     * @var    integer
     */
    public    $ID  = 0;
    protected $_ID = 0;

    /**
     * Payment type
     *
     * @since  0.0.1
     * @var string
     */
    protected $payment_type = '';

    /**
     * payment data
     *
     * @since  0.0.1
     * @var array
     */
    protected $payment_data = [];

    /**
     * The date the payment was created
     *
     * @since  0.0.1
     * @var string
     */
    protected $date = '';

    /**
     * The date the payment was marked as 'complete'
     *
     * @since  0.0.1
     * @var string
     */
    protected $completed_date = '';

    /**
     * The status of the payment
     *
     * @since  0.0.1
     * @var string
     */
    protected $status      = 'pending';
    protected $post_status = 'pending'; // Same as $status but here for backwards compat

    /**
     * The display name of the current payment status
     *
     * @since  0.0.1
     * @var string
     */
    protected $status_nicename = '';

    /**
     * The total amount the payment
     *
     * @since  0.0.1
     * @var float
     */
    protected $amount = 0.00;

    /**
     * The the payment currency
     *
     * @since  0.0.1
     * @var string
     */
    protected $currency = '';

    /**
     * The payment gateway
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
     * The payment user info
     *
     * @since  0.0.1
     * @var array
     */
    protected $customer = [];

    /**
     * The email used for the payment
     *
     * @since  0.0.1
     * @var string
     */
    protected $email = '';

    /**
     * The Unique Payment Key
     *
     * @since  0.0.1
     * @var string
     */
    protected $key = '';

    /**
     * The parent payment (if applicable)
     *
     * @since  0.0.1
     * @var integer
     */
    protected $parent_payment = 0;

    /**
     * The Gateway mode the payment was made in
     *
     * @since  0.0.1
     * @var string
     */
    protected $mode = 'live';

    /**
     * Identify if the payment is a new one or existing
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
     * This is for internal use, to allow fewer update_payment_meta calls to be run
     *
     * @since  0.0.1
     * @var array
     */
    private $pending = [];

    /**
     * Setup the smartpay Payments class
     *
     * @since  0.0.1
     * @param int $payment_id A given payment
     * @return mixed void|false
     */
    public function __construct($payment_or_txn_id = false, $by_txn = false)
    {
        global $wpdb;

        if (empty($payment_or_txn_id)) {
            return false;
        }

        if ($by_txn) {
            $query      = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_payment_transaction_id' AND meta_value = '%s'", $payment_or_txn_id);
            $payment_id = $wpdb->get_var($query);

            if (empty($payment_id)) {
                return false;
            }
        } else {
            $payment_id = absint($payment_or_txn_id);
        }

        $this->setup_payment($payment_id);
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
     * Setup payment properties
     *
     * @since  0.0.1
     * @param  int  $payment_id The payment ID
     * @return bool If the setup was successful or not
     */
    private function setup_payment($payment_id)
    {
        if (empty($payment_id)) {
            return false;
        }

        $payment = get_post($payment_id);
        if (!$payment || is_wp_error($payment)) {
            return false;
        }

        if ('smartpay_payment' !== $payment->post_type) {
            return false;
        }

        // Primary Identifier
        $this->ID               = absint($payment_id);

        // Protected ID that can never be changed
        $this->_ID              = absint($payment_id);

        $this->payment_type    = $this->setup_payment_type();
        $this->payment_data    = $this->setup_payment_data();

        // Status and Dates
        $this->date             = $payment->post_date;
        $this->completed_date   = $this->setup_completed_date();
        $this->status           = $payment->post_status;
        $all_payment_statuses   = smartpay_get_payment_statuses();
        $this->status_nicename  = array_key_exists($this->status, $all_payment_statuses) ? $all_payment_statuses[$this->status] : ucfirst($this->status);

        $this->amount           = $this->setup_amount();
        $this->currency         = $this->setup_currency();
        $this->gateway  = $this->setup_gateway();
        $this->transaction_id   = $this->setup_transaction_id();

        $this->customer         = $this->setup_customer();

        $this->email            = $this->setup_email();

        // Other Identifiers
        $this->key              = $this->setup_payment_key();

        $this->parent_payment   = $payment->post_parent;

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

            $payment_id = $this->insert_payment();

            if (false === $payment_id) {
                $saved = false;
            } else {
                $this->ID = $payment_id;
            }
        }

        if ($this->ID !== $this->_ID) {
            $this->ID = $this->_ID;
        }

        // If we have something pending, let's save it
        if (!empty($this->pending)) {

            foreach ($this->pending as $key => $value) {
                switch ($key) {
                    case 'payment_type':
                        $this->update_meta('_payment_type', $this->payment_type);
                        break;

                    case 'payment_data':
                        $this->update_meta('_payment_data', $this->payment_data);
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
                        $this->update_meta('_payment_completed_date', $this->completed_date);
                        break;

                    case 'status':
                        $this->update_status($this->status);
                        break;

                    case 'amount':
                        $this->update_meta('_payment_amount', $this->amount);
                        break;

                    case 'currency':
                        $this->update_meta('_payment_currency', $this->currency);
                        break;

                    case 'gateway':
                        $this->update_meta('_payment_gateway', $this->gateway);
                        break;

                    case 'transaction_id':
                        $this->update_meta('_payment_transaction_id', $this->transaction_id);
                        break;

                    case 'customer':
                        $this->update_meta('_payment_customer_data', $this->customer);
                        break;

                    case 'email':
                        $this->update_meta('_payment_email', $this->email);
                        break;

                    case 'key':
                        $this->update_meta('_payment_key', $this->key);
                        break;

                    case 'parent_payment':
                        $args = array(
                            'ID'          => $this->ID,
                            'post_parent' => $this->parent_payment,
                        );

                        wp_update_post($args);
                        break;

                    case 'mode':
                        $this->update_meta('_payment_mode', $this->mode);
                        break;

                    default:
                        /**
                         * Used to save non-standard data. Developers can hook here if they want to save
                         * specific payment data when $payment->save() is run and their item is in the $pending array
                         */
                        do_action('smartpay_payment_save', $this, $key);
                        break;
                }
            }

            $this->pending = array();
            $saved         = true;
        }

        if (true === $saved) {
            $this->setup_payment($this->ID);

            /**
             * This action fires anytime that $payment->save() is run, allowing developers to run actions
             * when a payment is updated
             */
            do_action('payment_data_payment_saved', $this->ID, $this);
        }

        /**
         * Update the payment in the object cache
         */
        // $cache_key = md5('payment_data_payment' . $this->ID);
        // wp_cache_set($cache_key, $this, 'payments');

        return $saved;
    }

    /**
     * Create the base of a payment.
     *
     * @since  0.0.1
     * @return int|bool False on failure, the payment ID on success.
     */
    private function insert_payment()
    {
        // Unique key
        $this->key = strtolower(md5($this->email . date('Y-m-d H:i:s') . rand(1, 10)));
        $this->pending['key'] = $this->key;

        // Create a blank payment
        $payment_id = wp_insert_post(array(
            'post_type'      => 'smartpay_payment',
            'post_status'    => $this->status ?? 'pending',
            'post_date'      => !empty($this->date) ? $this->date : null,
            'post_date_gmt'  => !empty($this->date) ? get_gmt_from_date($this->date) : null,
            'post_parent'    => $this->parent_payment,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if (!empty($payment_id)) {
            $this->ID   = $payment_id;
            $this->_ID  = $payment_id;

            $this->new  = true;
        }

        return $this->ID;
    }

    /**
     * Set the payment status and run any status specific changes necessary
     *
     * @since 0.0.1
     *
     * @param  string $status The status to set the payment to
     * @return bool Returns if the status was successfully updated
     */
    public function update_status($status = false)
    {
        if ($status == 'completed' || $status == 'complete') {
            $status = 'publish';
        }

        $old_status = !empty($this->old_status) ? $this->old_status : false;

        // Don't permit status changes that aren't changes
        if ($old_status === $status) return;

        $updated = false;

        do_action('smartpay_before_payment_status_change', $this, $status, $old_status);

        $update_fields = array('ID' => $this->ID, 'post_status' => $status, 'edit_date' => current_time('mysql'));

        $updated = wp_update_post(apply_filters('smartpay_update_payment_status_fields', $update_fields));

        $this->status = $status;
        $this->post_status = $status;

        $all_payment_statuses  = smartpay_get_payment_statuses();
        $this->status_nicename = array_key_exists($status, $all_payment_statuses) ? $all_payment_statuses[$status] : ucfirst($status);

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

        do_action('smartpay_update_payment_status', $this, $status, $old_status);

        return $updated;
    }

    /**
     * Get a post meta item for the payment
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

        $meta = apply_filters('smartpay_get_payment_meta_' . $meta_key, $meta, $this->ID);

        if (is_serialized($meta)) {
            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
            if (!empty($matches)) {
                $meta = array();
            }
        }

        return apply_filters('smartpay_get_payment_meta', $meta, $this->ID, $meta_key);
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

        $meta_value = apply_filters('smartpay_update_payment_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Add an item to the payment meta
     *
     * @since  0.0.1
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
     * Delete an item from payment meta
     *
     * @since  0.0.1
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
     * @return array The user info associated with the payment
     */
    private function setup_payment_type()
    {
        return $this->get_meta('_payment_type', true);
    }

    /**
     * Setup the user info
     *
     * @since  0.0.1
     * @return array The user info associated with the payment
     */
    private function setup_payment_data()
    {
        return $this->get_meta('_payment_data', true);
    }

    /**
     * Setup the payment completed date
     *
     * @since  0.0.1
     * @return string The date the payment was completed
     */
    private function setup_completed_date()
    {
        $payment = get_post($this->ID);

        if ('pending' == $payment->post_status || 'preapproved' == $payment->post_status || 'processing' == $payment->post_status) {
            return false; // This payment was never completed
        }

        $date = ($date = $this->get_meta('_payment_completed_date', true)) ? $date : $payment->date;

        return $date;
    }

    /**
     * Setup the payment amount
     *
     * @since  0.0.1
     * @return float The payment amount
     */
    private function setup_amount()
    {
        return $this->get_meta('_payment_amount', true);
    }

    /**
     * Setup the currency code
     *
     * @since  0.0.1
     * @return string The currency for the payment
     */
    private function setup_currency()
    {
        return $this->get_meta('_payment_currency', true) ?? smartpay_get_currency();
    }

    /**
     * Setup the payment gateway
     *
     * @since  0.0.1
     * @return string The payment gateway
     */
    private function setup_gateway()
    {
        return $this->get_meta('_payment_gateway');
    }

    /**
     * Setup the transaction ID
     *
     * @since  0.0.1
     * @return string The transaction ID for the payment
     */
    private function setup_transaction_id()
    {
        $transaction_id = $this->get_meta('_payment_transaction_id', true);

        if (empty($transaction_id) || (int) $transaction_id === (int) $this->ID) {

            $gateway        = $this->gateway;
            $transaction_id = apply_filters('smartpay_get_payment_transaction_id-' . $gateway, $this->ID);
        }

        return $transaction_id;
    }

    /**
     * Setup the customer for the payment
     *
     * @since  0.0.1
     * @return string The email address for the payment
     */
    private function setup_customer()
    {
        return $this->get_meta('_payment_customer_data', true) ?? [];
    }

    /**
     * Setup the email address for the payment
     *
     * @since  0.0.1
     * @return string The email address for the payment
     */
    private function setup_email()
    {
        return  $this->get_meta('_payment_email', true);
    }

    /**
     * Setup the payment key
     *
     * @since  0.0.1
     * @return string The Payment Key
     */
    private function setup_payment_key()
    {
        return $this->get_meta('_payment_key', true);
    }

    /**
     * Setup the payment mode
     *
     * @since  0.0.1
     * @return string The payment mode
     */
    private function setup_mode()
    {
        return $this->get_meta('_payment_mode');
    }

    public function complete_payment()
    {
        return $this->update_status('completed');
    }
}