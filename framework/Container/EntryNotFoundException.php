<?php

namespace SmartPay\Framework\Container;
defined('ABSPATH') || exit;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
