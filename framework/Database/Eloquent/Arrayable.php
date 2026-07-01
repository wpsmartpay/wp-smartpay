<?php

namespace SmartPay\Framework\Database\Eloquent;
defined('ABSPATH') || exit;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
