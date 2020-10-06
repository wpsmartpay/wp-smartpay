<?php

namespace SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

abstract class Integration
{
    abstract public static function config(): array;
}
