<?php
/**
 * Plugin Name: SmartPay
 * Description: Sell digital downloads and accept payments including donations easily with Stripe, PayPal, Paddle etc. - simple, fast, and secure.
 * Plugin URI:  https://wpsmartpay.com/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Tags: download manager, ecommerce, digital product, payment gateways, donations,
 *
 * Version: 2.8.2
 * Requires PHP: 8.1
 * Requires at least: 6.0
 * Tested up to: 6.8
 *
 * Author:      WPSmartPay
 * Author URI:  https://wpsmartpay.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
 * Text Domain: smartpay
 * Domain Path: /languages
 * License: GPLv3+
 *
 * @package WP SmartPay
 * @category Core
 *
 * WP SmartPay is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * WP SmartPay is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

defined('ABSPATH') || exit;

define('SMARTPAY_VERSION', '2.8.2');
define('SMARTPAY_PLUGIN_FILE', __FILE__);
define('SMARTPAY_PLUGIN_ASSETS', plugins_url('public', __FILE__));
define('SMARTPAY_STORE_URL', 'https://wpsmartpay.com/');

// Create The Application
$app = require __DIR__ . '/bootstrap.php';

global $smartpay_options;

$smartpay_options = smartpay_get_settings();

add_action('plugins_loaded', function () use ($app) {
    do_action('smartpay_loaded');

    // Run The Application
     $app->boot();
});

add_action('init', function () use ($app) {
    do_action('smartpay_init');
});
