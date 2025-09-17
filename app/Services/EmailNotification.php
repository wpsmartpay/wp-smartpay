<?php

namespace SmartPay\Services;

use SmartPay\Framework\Application;

// TODO: Refactor as notification
class EmailNotification
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Notify by email
     *
     * @param string  $mailTo
     * @param string $subject
     * @param string $body
     * @return boolean
     */
    public function notify(string $mailTo, string $subject, string $body): bool
    {
        try {
            return \wp_mail($mailTo, $subject, $body, $this->getEmailHeaders(), []);
        } catch (\Exception $e) {

            $log_message = sprintf(
				/* translators: 1: time 2: mail to 3: subject 4: error message */
                __('Email from SmartPay failed to send.\nSend time: %1$s\nTo: %2$s\nSubject: %3$s\nError: %4$s\n\n', 'smartpay'),
                date_i18n('F j Y H:i:s', current_time('timestamp')),
                $mailTo,
                $subject,
                $e->getMessage()
            );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($log_message);
        }

        return false;
    }

    /**
     * Get the email headers
     *
     */
    public function getEmailHeaders()
    {
        $fromName = smartpay_get_option('from_name', get_bloginfo('name'));

        $fromEmail = smartpay_get_option('from_email');

        if (empty($fromEmail) || !is_email($fromEmail)) {
            $fromEmail = get_option('admin_email');
        }

        $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        return apply_filters('smartpay_email_headers', $headers);
    }
}
