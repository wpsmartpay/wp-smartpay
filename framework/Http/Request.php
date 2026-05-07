<?php

namespace SmartPay\Framework\Http;
defined('ABSPATH') || exit;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class Request extends HttpFoundationRequest
{
    public function all()
    {
        return $this->request->all();
    }
}
