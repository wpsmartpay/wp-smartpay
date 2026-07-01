<?php

namespace SmartPay\Framework\Contracts\Container;
defined('ABSPATH') || exit;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class BindingResolutionException extends Exception implements ContainerExceptionInterface
{
    //
}
