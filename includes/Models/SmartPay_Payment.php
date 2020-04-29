<?php

namespace ThemesGrove\SmartPay\Models;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Payment extends Model
{
    public $id = 0;
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $amount = '';
    public $currency = '';
    public $gateway = '';
    public $form_id = '';
    public $status = '';

    /**
     * Setup the EDD Payments class
     *
     * @since 2.5
     * @param int $payment_id A given payment
     * @return mixed void|false
     */
    public function __construct($payment_id)
    {

        $post = get_post($payment_id);

        $this->id         = $post->ID;
        $this->first_name = get_post_meta($payment_id, '_first_name', true);
        $this->last_name  = get_post_meta($payment_id, '_last_name', true);
        $this->email      = get_post_meta($payment_id, '_email', true);
        $this->amount     = get_post_meta($payment_id, '_amount', true);
        $this->currency   = get_post_meta($payment_id, '_currency', true);
        $this->gateway    = get_post_meta($payment_id, '_gateway', true);
        $this->form_id    = get_post_meta($payment_id, '_form_id', true);
        $this->status     = $post->post_status;
    }

    public function complete_payment()
    {
        return wp_update_post(array(
            'ID'          => $this->payment_id,
            'post_status' => 'publish',
        ));
    }
}