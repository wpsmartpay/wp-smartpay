<?php

namespace SmartPay;

// Exit if accessed directly.
defined('ABSPATH') || exit;

abstract class Integration
{
    /**
     * Integration config
     *
     * @return array
     */
    abstract public static function config(): array;

    /**
     * Called when the integration is being activate
     *
     * @return boolean
     */
    abstract public function activate(): bool;

    /**
     * Called when the integration is being deactivate
     *
     * @return boolean
     */
    abstract public function deactivate(): bool;
}