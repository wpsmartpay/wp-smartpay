<?php

namespace SmartPay\Framework\Http;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class Request extends HttpFoundationRequest
{
    public function all()
    {
        return $this->request->all();
    }
}