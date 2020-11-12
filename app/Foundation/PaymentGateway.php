<?php

namespace SmartPay\Foundation;

abstract class PaymentGateway
{
    public function __construct()
    {
        //
    }

    public function registerGateway($gateways)
    {
        //
    }

    public function processPayment($paymentData)
    {
        //
    }

    public function ajaxProcessPayment($paymentData)
    {
        //
    }

    public function processRecurring()
    {
        //
    }

    public function processWebhooks()
    {
        //
    }
}