<?php

require_once __DIR__ . '/vendor/autoload.php';

// Create The Application
$app = new SmartPay\Framework\Application(__DIR__);

// Register Service Providers
$app->register(SmartPay\Providers\AppServiceProvider::class);
$app->register(SmartPay\Providers\AdminServiceProvider::class);

return $app;
