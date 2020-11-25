<?php

namespace SmartPay\Modules\Email;

use SmartPay\Framework\Application;
use SmartPay\Models\Payment;
use SmartPay\Services\EmailNotification;

class Email
{
    protected $app;

    protected $emailNotification;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->singleton(EmailNotification::class, static function ($app) {
            return new EmailNotification($app);
        });

        $this->emailNotification = $this->app->make(EmailNotification::class);

        $this->app->addAction('smartpay_payment_completed', [$this, 'sendPaymentReceipt'], 999, 1);
    }

    public function sendPaymentReceipt(Payment $payment)
    {
        // Subject
        $subject      = smartpay_get_option('payment_email_subject', __('Payment Receipt', 'smartpay'));
        $subject      = \wp_specialchars_decode($subject);

        // body
        $body = $this->getPaymentReceiptBody($payment);

        return $this->emailNotification->notify($payment->email, $subject, wpautop($body));
    }

    /**
     * Get email body
     *
     * @param Payment $payment
     * @return string
     */
    public function getPaymentReceiptBody(Payment $payment): string
    {
        switch ($payment->type) {
                // FIXME
            case 'Product Purchase':
                return smartpay_view('mail/payment-receipt/product-purchase', ['payment' => $payment]);
                break;

            case 'Form Payment':
                return smartpay_view('mail/payment-receipt/form-payment', ['payment' => $payment]);
                break;

            default:
                return '';
                break;
        }
    }
}
