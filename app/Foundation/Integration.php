<?php

namespace SmartPay\Foundation;

abstract class Integration
{
    /**
     * Integration config
     *
     * @return array
     */
    abstract public static function config(): array;

    /**
     * Called when the integration is boot
     *
     * @return void
     */
    abstract public function boot(): void;

    /**
     * Called when the integration is being activate
     *
     * @return boolean
     */
    public function activate() {
        //
    }

    /**
     * Called when the integration is being deactivate
     *
     * @return boolean
     */
    public function deactivate() {
        //
    }
}