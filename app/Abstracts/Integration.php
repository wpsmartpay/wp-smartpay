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
     * Called when the integration is boot
     *
     * @return void
     */
    public function boot(){
		//
	}

    /**
     * Called when the integration is being activate
     *
     * @return boolean
     */
    public function activate(){
		//
	}

    /**
     * Called when the integration is being deactivate
     *
     * @return boolean
     */
    public function deactivate(){
		//
	}
}
