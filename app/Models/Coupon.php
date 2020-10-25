<?php

namespace SmartPay\Models;

defined('ABSPATH') || exit;

use SmartPay\Models\Model;

class Coupon extends Model
{
    /**
     * The coupon code
     *
     * @var string
     */
    protected $code = '';

	/**
     * The coupon description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The coupon discount type
     *
     * @var string
     */
    protected $discount_type = '';

    /**
     * The coupon discount amount
     *
     * @var string
     */
    protected $discount_amount = '';

    /**
     * The coupon expire date
     *
     * @var string
     */
    protected $expiry_date = '';

    /**
     * Get things going
     *
     */
    public function __construct($_id = false)
    {
        if (!$_id) {
            return;
        }

        $coupon = \WP_Post::get_instance($_id);

        $this->setup_coupon($coupon);
    }

    /**
     * Given the coupon data, let's set the variables
     *
     * @param $coupon
     */
    private function setup_coupon($coupon)
    {
        if (!is_object($coupon)) {
            return false;
        }

        if (!$coupon instanceof \WP_Post) {
            return false;
        }

        if ('smartpay_coupon' !== $coupon->post_type) {
            return false;
        }

        $this->ID = absint($coupon->ID);
        $this->_ID = absint($coupon->ID); // Protected ID that can never be changed

        $this->code             = $coupon->post_title;
        $this->description      = $this->_setup_description();
        $this->discount_type    = $this->_setup_discount_type();
        $this->discount_amount  = $this->_setup_discount_amount();
        $this->expiry_date      = $this->_setup_expiry_date();
        $this->status           = $coupon->post_status;

        return true;
	}

	public function get_meta($meta_key = '', $single = true)
    {
        if (empty($meta_key)) {
            return;
        }

        $meta = get_post_meta($this->ID, $meta_key, $single);

        $meta = apply_filters('smartpay_get_coupon_meta_' . $meta_key, $meta, $this->ID);

        if (is_serialized($meta)) {
            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches);
            if (!empty($matches)) {
                $meta = array();
            }
        }

        return apply_filters('smartpay_get_coupon_meta', $meta, $this->ID, $meta_key);
	}

	public function update_meta($meta_key = '', $meta_value = '', $prev_value = '')
    {
        if (empty($meta_key)) {
            return;
        }

        $meta_value = apply_filters('smartpay_coupon_update_meta_' . $meta_key, $meta_value, $this->ID);

        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Setup the discount type
     *
     * @return string
     */
    private function _setup_discount_type()
    {
        return $this->get_meta('_discount_type') ?? '';
	}

    private function _setup_description()
    {
        return $this->get_meta('_description') ?? '';
    }

    /**
     * Setup the discount amount
     *
     * @return float
     */
    private function _setup_discount_amount()
    {
        $discount_amount = $this->get_meta('_discount_amount');

        return !empty($discount_amount) ? (float) $discount_amount : null;
	}

    /**
     * Setup the expiry date
     *
     * @return string
     */
    private function _setup_expiry_date()
    {
        $expiry_date = $this->get_meta('_expiry_date');

        return !empty($expiry_date) ? $expiry_date : null;
    }

    public function __get($key)
    {
        if (property_exists($this, $key)) {
			return apply_filters( 'smartpay_coupon_get_' . $key, $this->$key, $this );

        } else {
            return new \WP_Error('smartpay-coupon-invalid-property', sprintf(__('Can\'t get property %s', 'smartpay'), $key));
        }
    }

    /**
     * Create the base of a coupon.
     *
     * @return int|bool False on failure, the coupon ID on success.
     */
    private function insert()
    {
        if (0 != $this->ID) {
            return false;
		}

        // Create a blank coupon
        $coupon_id = wp_insert_post(array(
            'post_title'     => $this->title ?? '',
            'post_type'      => 'smartpay_coupon',
            'post_status'    => $this->status ?? 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if (!empty($coupon_id)) {
            $this->ID   = $coupon_id;
            $this->_ID  = $coupon_id;

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
            $coupon_id = $this->insert();

            if (false === $coupon_id) {
                $saved = false;
            } else {
                $this->ID = $coupon_id;
            }
        }

        if ($this->ID !== $this->_ID) {
            $this->ID = $this->_ID;
        }

        // If we have something pending, let's save it
        if (!empty($this->pending)) {
            foreach ($this->pending as $key => $value) {
                switch ($key) {
                    case 'code':
                        echo 'code <br>';
                        wp_update_post(array('ID' => $this->ID, 'post_title' => $this->code));
                        break;

                    case 'description':
                        echo 'description <br>';
                        $this->update_meta('_description', $this->description);
						break;

                    case 'discount_type':
                        echo 'discount_type <br>';
                        $this->update_meta('_discount_type', $this->discount_type);
                        break;

                    case 'discount_amount':
                        echo 'discount_amount <br>';
                        $this->update_meta('_discount_amount', $this->discount_amount);
						break;

                    case 'expiry_date':
                        echo 'expiry_date <br>';
                        $this->update_meta('_expiry_date', $this->expiry_date);
                        break;

                    case 'status':
                        echo 'status <br>';
                        wp_update_post(array('ID' => $this->ID, 'post_status' => $this->status));
                        break;

                    default:
                        /** Used to save non-standard data. Developers can hook here if they want to save **/
                        do_action('smartpay_coupon_data_save', $this, $key);
                        break;
                }
            }

            $this->pending = [];
            $saved         = true;
        }

        if (true === $saved) {
            $this->setup_coupon($this->ID);

            /** This action fires anytime that $coupon->save() is run **/
            do_action('smartpay_coupon_saved', $this->ID, $this);
		}

        return $saved;
    }
}