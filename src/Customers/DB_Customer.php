<?php

namespace SmartPay\Customers;

use SmartPay\DB_Model;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class DB_Customer extends DB_Model
{
    /**
     * Get things started
     *
     * @since  0.0.1
     */
    public function __construct()
    {
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'smartpay_customers';
        $this->version     = '1.0';
        $this->primary_key = 'ID';
        $this->cache_group = 'customers';

        // add_action('profile_update', array($this, 'update_customer_email_on_user_update'), 10, 2);
    }

    /**
     * Get columns and formats
     *
     * @since  0.0.1
     */
    public function get_columns()
    {
        return array(
            'ID'             => '%d',
            'user_id'        => '%d',
            'first_name'     => '%s',
            'last_name'      => '%s',
            'email'          => '%s',
            'payments'       => '%s',
            'notes'          => '%s',
            'created_at'     => '%s',
        );
    }

    /**
     * Get default column values
     *
     * @since  0.0.1
     */
    public function get_column_defaults()
    {
        return array(
            'user_id'        => 0,
            'first_name'     => '',
            'last_name'      => '',
            'email'          => '',
            'payments'    => '',
            'notes'          => '',
            'created_at'     => date('Y-m-d H:i:s'),
        );
    }

    /**
     * Insert a new customer
     *
     * @since  0.0.1
     * @return  int
     */
    public function insert($data, $type = '')
    {
        $customer_id = parent::insert($data, $type);

        return $customer_id;
    }

    /**
     * Update a customer
     *
     * @since  0.0.1
     * @return  bool
     */
    public function update($row_id = 0, $data = array(), $where = '')
    {
        if (!$row_id) return;

        $result = parent::update($row_id, $data, $where);

        return $result;
    }

    /**
     * Delete a customer
     *
     * @since  0.0.1
     * @return  bool
     */
    public function delete($row_id = 0)
    {
        if (!$row_id) return;

        $result = parent::delete($row_id);

        return $result;
    }

    /**
     * Checks if a customer exists
     *
     * @since  0.0.1
     */
    public function row_exists($value = '', $field = 'email')
    {
        $columns = $this->get_columns();
        if (!array_key_exists($field, $columns)) {
            return false;
        }

        return (bool) $this->get_column_by('id', $field, $value);
    }

    /**
     * Updates the email address of a customer record when the email on a user is updated
     *
     * @since  0.0.1
     */
    public function update_customer_email_on_user_update($user_id = 0, $old_user_data)
    {

        $customer = new SmartPay_Customer($user_id);

        if (!$customer) {
            return false;
        }

        $user = get_userdata($user_id);

        if (!empty($user) && $user->user_email !== $customer->email) {

            if (!$this->get_customer_by('email', $user->user_email)) {

                $result = $this->update($customer->id, array('email' => $user->user_email));

                if ($result) {
                    // Update some payment meta if we need to
                    $payments_array = explode(',', $customer->payments);

                    if (!empty($payments_array)) {

                        foreach ($payments_array as $payment_id) {

                            // smartpay_update_payment_meta($payment_id, 'email', $user->user_email);
                        }
                    }

                    do_action('smartpay_update_customer_email_on_user_update', $user, $customer);
                }
            }
        }
    }

    /**
     * Retrieves a single customer from the database
     *
     * @since  0.0.1
     * @param  string $column id or email
     * @param  mixed  $value  The Customer ID or email to search
     * @return mixed  Upon success, an object of the customer. Upon failure, false
     */
    public function get_customer_by($column = 'id', $value = 0)
    {
        if (empty($column) || empty($value)) return;

        if ('id' == $column || 'user_id' == $column) {

            if (!is_numeric($value)) return;

            $value = intval($value);

            if ($value < 1) return;
        } elseif ('email' == $column) {

            if (!is_email($value)) return;

            $value = trim($value);
        } else {
            return;
        }

        if (!$value) return;

        $result = parent::get_by($column, $value);

        return $result;
    }

    /**
     * Retrieve customers from the database
     *
     * @since  0.0.1
     */
    public function get_customers($args = array())
    {
        $args = $this->prepare_customer_query_args($args);
        $args['count'] = false;

        $query = new Customer_Query('', $this);

        return $query->query($args);
    }

    /**
     * Count the total number of customers in the database
     *
     * @since  0.0.1
     */
    public function count($args = array())
    {
        $args = $this->prepare_customer_query_args($args);
        $args['count'] = true;
        $args['offset'] = 0;

        $query   = new Customer_Query('', $this);
        $results = $query->query($args);

        return $results;
    }

    /**
     * Prepare query arguments for `Customer_Query`.
     *
     * This method ensures that old arguments transition seamlessly to the new system.
     *
     * @access protected
     * @since  0.0.1
     *
     * @param array $args Arguments for `Customer_Query`.
     * @return array Prepared arguments.
     */
    protected function prepare_customer_query_args($args)
    {
        if (!empty($args['id'])) {
            $args['include'] = $args['id'];
            unset($args['id']);
        }

        if (!empty($args['user_id'])) {
            $args['users_include'] = $args['user_id'];
            unset($args['user_id']);
        }

        if (!empty($args['name'])) {
            $args['search'] = '***' . $args['name'] . '***';
            unset($args['name']);
        }

        if (!empty($args['date'])) {
            $date_query = array('relation' => 'AND');

            if (is_array($args['date'])) {
                $date_query[] = array(
                    'after'     => date('Y-m-d 00:00:00', strtotime($args['date']['start'])),
                    'inclusive' => true,
                );
                $date_query[] = array(
                    'before'    => date('Y-m-d 23:59:59', strtotime($args['date']['end'])),
                    'inclusive' => true,
                );
            } else {
                $date_query[] = array(
                    'year'  => date('Y', strtotime($args['date'])),
                    'month' => date('m', strtotime($args['date'])),
                    'day'   => date('d', strtotime($args['date'])),
                );
            }

            if (empty($args['date_query'])) {
                $args['date_query'] = $date_query;
            } else {
                $args['date_query'] = array(
                    'relation' => 'AND',
                    $date_query,
                    $args['date_query'],
                );
            }

            unset($args['date']);
        }

        return $args;
    }

    /**
     * Sets the last_changed cache key for customers.
     *
     * @since  0.0.1
     */
    public function set_last_changed()
    {
        wp_cache_set('last_changed', microtime(), $this->cache_group);
    }

    /**
     * Retrieves the value of the last_changed cache key for customers.
     *
     * @since  0.0.1
     */
    public function get_last_changed()
    {
        if (function_exists('wp_cache_get_last_changed')) {
            return wp_cache_get_last_changed($this->cache_group);
        }

        $last_changed = wp_cache_get('last_changed', $this->cache_group);
        if (!$last_changed) {
            $last_changed = microtime();
            wp_cache_set('last_changed', $last_changed, $this->cache_group);
        }

        return $last_changed;
    }

    /**
     * Create the table
     *
     * @since  0.0.1
     */
    public function create_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE " . $this->table_name . " (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		first_name mediumtext NOT NULL,
		last_name mediumtext NOT NULL,
		email varchar(50) NOT NULL,
		payments longtext NOT NULL,
		notes longtext NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta($sql);

        update_option($this->table_name . '_db_version', $this->version);
    }
}