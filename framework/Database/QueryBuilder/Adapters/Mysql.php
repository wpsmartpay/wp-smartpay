<?php

namespace SmartPay\Framework\Database\QueryBuilder\Adapters;
defined('ABSPATH') || exit;

class Mysql extends BaseAdapter
{
    /**
     * @var string
     */
    protected $sanitizer = '`';
}
