<?php

namespace SmartPay\Customers;

use SmartPay\Payments\SmartPay_Payment;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Customer
{
    /**
     * The Customer ID
     *
     * @since  0.0.1
     * @var    integer
     */
    public    $ID  = 0;
    protected $_ID = 0;

    /**
     * Customer user_id
     *
     * @since  0.0.1
     * @var int
     */
    protected $user_id = '';

    /**
     * Customer wp_user
     *
     * @since  0.0.1
     * @var object
     */
    // protected $wp_user = '';

    /**
     * Customer first_name
     *
     * @since  0.0.1
     * @var string
     */
    protected $first_name = '';

    /**
     * Customer last_name
     *
     * @since  0.0.1
     * @var string
     */
    protected $last_name = '';

    /**
     * Customer email
     *
     * @since  0.0.1
     * @var string
     */
    protected $email = '';

    /**
     * Customer payments
     *
     * @since  0.0.1
     * @var array
     */
    protected $payments = [];

    /**
     * Customer notes
     *
     * @since  0.0.1
     * @var string
     */
    protected $notes = '';

    /**
     * Customer date created
     *
     * @since  0.0.1
     * @var string
     */
    protected $created_at = '';

    /**
     * Setup the smartpay customers class
     *
     * @since 0.0.1
     * @param int $customer_id A given customer
     * @return mixed void|false
     */
    public function __construct($_id_or_email = false, $by_user_id = false)
    {
        if (false === $_id_or_email || (is_numeric($_id_or_email) && (int) $_id_or_email !== absint($_id_or_email))) {
            return false;
        }

        $by_user_id = is_bool($by_user_id) ? $by_user_id : false;

        if (is_numeric($_id_or_email)) {
            $field = $by_user_id ? 'user_id' : 'id';
        } else {
            $field = 'email';
        }


        $customer = (new DB_Customer)->get_customer_by($field, $_id_or_email);

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
        }

        if ('_ID' !== $key) {
            $this->$key = $value;
        }
    }

    /**
     * Setup customer properties
     *
     * @since  0.0.1
     * @param  object  $customer
     * @return bool If the setup was successful or not
     */
    private function setup_customer($customer)
    {
        if (empty($customer)) return;

        // Protected ID that can never be changed
        $this->_ID          = absint($customer->ID);

        // Primary Identifier
        $this->ID           = absint($customer->ID);

        $this->user_id      = absint($customer->user_id);
        // $this->wp_user      = get_userdata($this->user_id);
        $this->first_name   = $customer->first_name;
        $this->last_name    = $customer->last_name;
        $this->email        = $customer->email;
        $this->payments     = maybe_unserialize($customer->payments) ?? [];

        $this->notes        = $customer->notes;
        $this->created_at   = $customer->created_at;

        return true;
    }

    public function insert()
    {
        $customer_db = new DB_Customer;

        $data = array(
            'first_name' => $this->first_name,
            'user_id'    => $this->user_id,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'payments'   => maybe_serialize($this->payments ?? [])
        );

        $data = array_merge($customer_db->get_column_defaults(), $data);

        return $customer_db->insert($data, 'customer');
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

            $payment_id = $this->insert();

            if (false === $payment_id) {
                $saved = false;
            } else {
                $this->ID = $payment_id;
            }
        }

        // TODO: Complete it
    }

    public function attach_payment($payment_id = 0)
    {
        if (!$payment_id || !is_array($this->payments)) return;

        $payment_id = intval($payment_id);

        if (!in_array($payment_id, $this->payments)) {
            array_push($this->payments, $payment_id);
        }

        return (new DB_Customer)->update($this->_ID, ['payments' => \maybe_serialize($this->payments)]);
    }

    public function all_payments()
    {
        return array_map(function ($payment_id) {

            $payment = new SmartPay_Payment(intval($payment_id));

            if ($payment->ID > 0 && $payment->customer['customer_id'] == $this->_ID) {
                return $payment;
            }
        }, $this->payments);
    }

    public static function create($data)
    {
        $customer = new self();

        $customer->user_id      = $data['user_id'] ?? 0;
        $customer->first_name   = $data['first_name'];
        $customer->last_name    = $data['last_name'];
        $customer->email        = $data['email'];
        $customer->payments     = $data['payments'];

        return $customer->insert();
    }

    public static function exists($id_or_email = 0)
    {
        if (!$id_or_email) return;

        if (is_numeric($id_or_email)) {
            $field = 'id';
        } else if (is_email($id_or_email)) {
            $field = 'email';
        } else {
            return;
        }

        return (new DB_Customer)->row_exists($id_or_email, $field);
    }
}