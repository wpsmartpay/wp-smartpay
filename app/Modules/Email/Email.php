<?php

namespace SmartPay\Modules\Email;

class Email
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
}