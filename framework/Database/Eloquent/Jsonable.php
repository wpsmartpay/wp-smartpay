<?php

namespace SmartPay\Framework\Database\Eloquent;
defined('ABSPATH') || exit;

interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
