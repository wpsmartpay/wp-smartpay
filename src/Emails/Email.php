<?php

namespace SmartPay\Emails;

use SmartPay\Payments\SmartPay_Payment;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Email
{
    /**
     * Holds the from name
     * 
     * @since 0.0.2
     **/
    private $from_name = '';

    /**
     * Holds the from email
     * 
     * @since 0.0.2
     **/
    private $from_email = '';

    /**
     * Holds the content type
     * 
     * @since 0.0.2
     **/
    private $content_type = 'text/html';

    /**
     * Holds the content type is html
     * 
     * @since 0.0.2
     **/
    private $html = true;

    /**
     * Holds the headers
     * 
     * @since 0.0.2
     **/
    private $headers = '';

    /**
     * The single instance of this class
     * 
     * @since 0.0.2
     **/
    private static $instance = null;

    /**
     * Construct Email class.
     *
     * @since 0.0.2
     * @access private
     */
    private function __construct()
    {
        add_action('smartpay_complete_payment', [$this, 'send_payment_receipt'], 999, 1);
    }

    /**
     * Main Email Instance.
     *
     * Ensures that only one instance of Email exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.2
     * @return object|Email
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Actions)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the email from name
     *
     * @since 0.0.2
     */
    public function get_from_name()
    {
        if (!$this->from_name) {
            $this->from_name = smartpay_get_option('from_name', get_bloginfo('name'));
        }

        return apply_filters('smartpay_email_from_name', wp_specialchars_decode($this->from_name), $this);
    }

    /**
     * Get the email from address
     *
     * @since 0.0.2
     */
    public function get_from_email()
    {
        if (!$this->from_email) {
            $this->from_email = smartpay_get_option('from_email');
        }

        if (empty($this->from_email) || !is_email($this->from_email)) {
            $this->from_email = get_option('admin_email');
        }

        return apply_filters('smartpay_email_from_email', $this->from_email, $this);
    }

    /**
     * Get the email content type
     *
     * @since 0.0.2
     */
    public function get_content_type()
    {
        if ($this->html) {
            $this->content_type = 'text/html';
        } else {
            $this->content_type = 'text/plain';
        }

        return apply_filters('smartpay_email_content_type', $this->content_type, $this);
    }

    /**
     * Get the email headers
     *
     * @since 0.0.2
     */
    public function get_headers()
    {
        if (!$this->headers) {
            $this->headers  = "From: {$this->get_from_name()} <{$this->get_from_email()}>\r\n";
            $this->headers .= "Reply-To: {$this->get_from_email()}\r\n";
            $this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
        }

        return apply_filters('smartpay_email_headers', $this->headers, $this);
    }

    /**
     * Email the download link(s) and payment confirmation to user
     *
     * @since 0.0.2
     *
     * @return void
     */
    public function send_payment_receipt($payment_id)
    {
        if (!$payment_id) return;

        $payment = new SmartPay_Payment(absint($payment_id));

        $to_email = $payment->email;

        // Email Subject
        $subject      = smartpay_get_option('payment_email_subject', __('Payment Receipt', 'smartpay'));
        $subject      = \wp_specialchars_decode($subject);

        // Heading
        $heading      = smartpay_get_option('payment_email_heading', __('Payment Receipt', 'smartpay'));

        // $attachments  = apply_filters('smartpay_receipt_attachments', array(), $payment_id, $payment_data);

        // Email body
        $body = '';

        if ('product_purchase' == $payment->payment_type) {
            $body = smartpay_view_render('mail/payment_receipt/product_purchase', ['payment' => $payment]);
        } else if ('form_payment' == $payment->payment_type) {
            $body = smartpay_view_render('mail/payment_receipt/form_payment', ['payment' => $payment]);
        }

        $email = new SmartPay_Email;
        $email->to_email    = $to_email;
        $email->subject     = $subject;
        $email->body        = wpautop($body);
        $email->headers     = $this->get_headers();
        $email->attachments = '';

        $email->send();
    }
}