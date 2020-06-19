<?php

namespace SmartPay\Emails;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SmartPay_Email
{
    /**
     * Holds the to email
     *
     * @since x.x.x
     */
    private $to_email = '';

    /**
     * Holds the email headers
     *
     * @since x.x.x
     */
    private $headers = '';

    /**
     * The subject for the email
     *
     * @since  x.x.x
     */
    private $subject = '';

    /**
     * The body for the email
     *
     * @since  x.x.x
     */
    private $body = '';

    /**
     * The attachments for the email
     *
     * @since  x.x.x
     */
    private $attachments = '';

    /**
     * Construct Email class.
     *
     * @since x.x.x
     * @access private
     */
    public function __construct()
    {
    }

    /**
     * Set a property
     *
     * @since x.x.x
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Get a property
     *
     * @since x.x.x
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * Send the email
     * 
     * @since x.x.x
     */
    public function send()
    {
        try {
            /** Hooks before the email is sent **/
            do_action('smartpay_email_send_before', $this);

            $sent = \wp_mail($this->to_email, $this->subject, $this->body, $this->headers, $this->attachments);

            /** Hooks after the email is sent **/
            do_action('smartpay_email_send_after', $this);

            if (!$sent) {
                $log_message = sprintf(
                    __("Email from SmartPay failed to send.\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'smartpay'),
                    date_i18n('F j Y H:i:s', current_time('timestamp')),
                    $this->to_email,
                    $this->subject,
                );
                error_log($log_message);
                return false;
            }
        } catch (\Exception $e) {

            $log_message = sprintf(
                __("Email from SmartPay failed to send.\nSend time: %s\nTo: %s\nSubject: %s\n\Error: %s\n\n", 'smartpay'),
                date_i18n('F j Y H:i:s', current_time('timestamp')),
                $this->to_email,
                $this->subject,
                $e->getMessage()
            );
            error_log($log_message);
            return false;
        }

        return true;
    }
}