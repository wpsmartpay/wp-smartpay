<?php

namespace SmartPay\Modules\Email;

use SmartPay\Models\Payment;

class Email
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('smartpay_payment_completed', [$this, 'sendEmailReceipt'], 10, 1);
    }

    public function sendEmailReceipt(Payment $payment)
    {
        // Send Email
    }
}
