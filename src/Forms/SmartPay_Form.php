<?php

namespace SmartPay\Forms;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Form
{
    /**
     * The form ID
     *
     * @since  0.0.1
     * @var integer
     */
    public $ID = 0;
    protected $_ID = 0;

    /**
     * The form title
     *
     * @since  0.0.1
     * @var string
     */
    protected $title = '';

    /**
     * The form description
     *
     * @since  0.0.1
     * @var string
     */
    protected $description = '';

    /**
     * The form image
     *
     * @since  0.0.1
     * @var string
     */
    protected $image = '';

    /**
     * The form payment type
     *
     * @since  0.0.1
     * @var string
     */
    protected $payment_type = 'one-time';

    /**
     * The form amounts
     *
     * @since  0.0.1
     * @var array
     */
    protected $amounts = array();

    /**
     * The form has multiple amount
     *
     * @since  0.0.1
     * @var boolean
     */
    protected $has_multiple_amount = false;

    /**
     * The form accept custom amount
     *
     * @since  0.0.1
     * @var boolean
     */
    protected $allow_custom_amount = false;

    /**
     * The form status
     *
     * @since  0.0.1
     * @var array
     */
    protected $status = 'publish';

    /**
     * The form created_at time
     *
     * @since  0.0.1
     * @var integer
     */
    protected $created_at = '';

    /**
     * The form updated_at time
     *
     * @since  0.0.1
     * @var integer
     */
    protected $updated_at = '';

    /**
     * Identify if the form is a new one or existing
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
        $_id = absint($_id);

        if (!$_id) return;

        $form = \WP_Post::get_instance($_id);

        return $this->setup_form($form);
    }

    /**
     * Given the form data, let's set the variables
     *
     * @since  0.0.1
     * @param  \WP_Post $form
     * @return bool If the setup was successful or not
     */
    private function setup_form($form)
    {
        if (!is_object($form) || !$form instanceof \WP_Post) return;

        if ('smartpay_form' !== $form->post_type) return;

        // Primary Identifier
        $this->ID = absint($form->ID);

        // Protected ID that can never be changed
        $this->_ID = absint($form->ID);

        $this->title                = $form->post_title;
        $this->description          = $form->post_content;
        $this->image                = $this->_setup_image();
        $this->payment_type         = $this->_setup_payment_type();
        $this->amounts              = $this->_setup_amounts();
        $this->has_multiple_amount  = $this->has_multiple_amount();
        $this->allow_custom_amount = $this->_setup_allow_custom_amount();
        $this->status               = $form->post_status;
        $this->created_at           = $form->post_date;
        $this->updated_at           = $form->post_modified;

        return true;
    }

    /**
     * Get a post meta item for the form
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

        $meta = apply_filters('smartpay_get_form_meta_' . $meta_key, $meta, $this->ID);

        if (is_serialized($meta)) {
            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
            if (!empty($matches)) {
                $meta = array();
            }
        }

        return apply_filters('smartpay_get_form_meta', $meta, $this->ID, $meta_key);
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
        if (empty($meta_key)) return;

        $meta_value = apply_filters('smartpay_form_update_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Setup the image
     *
     * @since  0.0.1
     * @return float Form image
     */
    private function _setup_image()
    {
        return has_post_thumbnail($this->ID) ? wp_get_attachment_url(get_post_thumbnail_id($this->ID), 'thumbnail') : '';
    }

    /**
     * Setup the payment_type
     *
     * @since  0.0.1
     * @return float payment type associated with the form
     */
    private function _setup_payment_type()
    {
        $payment_type = $this->get_meta('_form_payment_type');

        return $payment_type;
    }

    /**
     * Setup the amounts
     *
     * @since  0.0.1
     * @return float The amounts associated with the form
     */
    private function _setup_amounts()
    {
        $amounts = $this->get_meta('_form_amounts');

        if (!is_array($amounts) || !count($amounts)) return [];

        return $amounts;
    }

    /**
     * Determine if the form has multiple amount enabled
     *
     * @since  0.0.1
     * @return bool True when the form has multiple amount, false otherwise
     */
    public function has_multiple_amount()
    {
        $has_multiple_amount = count($this->amounts) > 1 ? true : false;

        // Override whether the form has multiple amount.
        return (bool) apply_filters('smartpay_form_has_multiple_amount', $has_multiple_amount, $this->ID);
    }

    /**
     * Determine if the form has multiple amount enabled
     *
     * @since  0.0.1
     * @return bool True when the form has multiple amount, false otherwise
     */
    public function _setup_allow_custom_amount()
    {
        $allow_custom_amount = $this->get_meta('_form_allow_custom_amount');

        // Override whether the form accept custom amount.
        return (bool) apply_filters('smartpay_form_allow_custom_amount', $allow_custom_amount, $this->ID);
    }

    /**
     * Magic __get function to dispatch a call to retrieve a private property
     *
     * @since  0.0.1
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
        if (!in_array($key, ['_ID'])) {
            $this->pending[$key] = $value;
            $this->$key = $value;
        }
    }

    /**
     * Retrieve the ID
     *
     * @since  0.0.1
     * @return int ID of the form
     */
    public function get_ID()
    {
        return $this->ID;
    }

    /**
     * Retrieve the title
     *
     * @since  0.0.1
     * @return string title of the form
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * Retrieve the description
     *
     * @since  0.0.1
     * @return string description of the form
     */
    public function get_description()
    {
        return $this->description;
    }

    /**
     * Retrieve the image
     *
     * @since  0.0.1
     * @return string image of the form
     */
    public function get_image()
    {
        // Override the form base price.
        return apply_filters('smartpay_form_get_image', $this->image, $this->ID);
    }

    /**
     * Retrieve the amounts
     *
     * @since  0.0.1
     * @return string amounts of the form
     */
    public function get_amounts()
    {
        return apply_filters('smartpay_form_get_amounts', $this->amounts, $this->ID);
    }

    /**
     * Retrieve the form status
     *
     * @since  0.0.1
     * @return string Status of the form
     */
    public function get_status()
    {
        return apply_filters('smartpay_form_get_status', $this->status, $this->ID);
    }

    /**
     * Create the base of a form.
     *
     * @since  0.0.1
     * @return int|bool False on failure, the form ID on success.
     */
    private function _insert()
    {
        if (0 != $this->ID) return;

        // Create a blank form
        $form_id = wp_insert_post(array(
            'post_title'     => $this->title ?? '',
            'post_content'   => $this->description ?? '',
            'post_type'      => 'smartpay_form',
            'post_status'    => $this->status ?? 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if (!empty($form_id)) {
            $this->ID   = $form_id;
            $this->_ID  = $form_id;

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
            $form_id = $this->_insert();

            if (false === $form_id) {
                $saved = false;
            } else {
                $this->ID = $form_id;
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

                    case 'payment_type':
                        $this->update_meta('_form_payment_type', $this->payment_type);
                        break;

                    case 'amounts':
                        $this->update_meta('_form_amounts', $this->amounts);
                        break;

                    case 'allow_custom_amount':
                        $this->update_meta('_form_allow_custom_amount', $this->allow_custom_amount);
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
                        do_action('smartpay_form_data_save', $this, $key);
                        break;
                }
            }

            $this->pending = array();
            $saved         = true;
        }

        if (true === $saved) {
            $this->setup_form($this->ID);

            /** This action fires anytime that $form->save() is run **/
            do_action('smartpay_form_saved', $this->ID, $this);
        }

        return $saved;
    }

    /**
     * Checks if the form can be paid
     *
     * @since  0.0.1
     * @return bool If the current user can pay the form ID
     */
    public function can_pay()
    {
        $can_pay = true;

        if ($this->status != 'publish') {
            $can_pay = false;
        }

        return (bool) apply_filters('smartpay_can_pay_form', $can_pay, $this);
    }
}
