<?php

use SmartPay\Activator;
use SmartPay\Deactivator;
use SmartPay\Updater;

require_once __DIR__ . '/vendor/autoload.php';

// Create The Application
$app = new SmartPay\Framework\Application(__DIR__);

// Register Service Providers
$app->register(SmartPay\Providers\AppServiceProvider::class);
$app->register(SmartPay\Providers\AdminServiceProvider::class);
$app->register(SmartPay\Providers\RouteServiceProvider::class);
$app->register(SmartPay\Providers\IntegrationServiceProvider::class);

register_activation_hook(SMARTPAY_PLUGIN_FILE, [Activator::class, 'boot']);

register_deactivation_hook(SMARTPAY_PLUGIN_FILE, [Deactivator::class, 'boot']);

Updater::boot();

add_action('plugins_loaded', function () {
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    if (defined('SMARTPAY_PRO_VERSION')) {
        if (floatval(SMARTPAY_PRO_VERSION) < 2.6){
            add_action('admin_notices', 'smartpay_pro_deactivate_notice');
            deactivate_plugins(SMARTPAY_PRO_PLUGIN_FILE);
        }
    }
}, 20);

function smartpay_pro_deactivate_notice(){
    echo __('<div class="error notice-warning"><p><code>WP SmartPay Pro '.SMARTPAY_PRO_VERSION. '</code> is not compatible with <code>WP SmartPay version 2.6.0</code> or higher. Please update the <code>WP SmartPay Pro</code> or downgrade the <code>WP SmartPay bellow 2.6.0</code>.</p></div>', 'smartpay');
}

return $app;
