<?php

namespace SmartPay\Framework\Database\Eloquent;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
