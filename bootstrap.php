<?php

use SmartPay\Activator;
use SmartPay\Deactivator;

require_once __DIR__ . '/vendor/autoload.php';

// Create The Application
$app = new SmartPay\Framework\Application(__DIR__);

// Register Service Providers
$app->register(SmartPay\Providers\AppServiceProvider::class);
$app->register(SmartPay\Providers\AdminServiceProvider::class);
$app->register(SmartPay\Providers\RouteServiceProvider::class);

register_activation_hook(SMARTPAY_PLUGIN_FILE, [Activator::class, 'boot']);

register_deactivation_hook(SMARTPAY_PLUGIN_FILE, [Deactivator::class, 'boot']);

return $app;
