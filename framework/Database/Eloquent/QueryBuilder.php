<?php

namespace SmartPay\Framework\Database\Eloquent;

use SmartPay\Framework\Database\Connection;
use SmartPay\Framework\Database\QueryBuilder\QueryBuilderHandler;

class QueryBuilder
{
    protected $query = null;

    public function __construct()
    {
        global $wpdb;

        $connection = new Connection($wpdb, ['prefix' => $wpdb->prefix]);

        $this->query = new QueryBuilderHandler($connection);
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->query, $method], $params);
    }
}