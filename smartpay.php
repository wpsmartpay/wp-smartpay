<?php

/**
 * Plugin Name: SmartPay
 * Description: Simplest way to sell digital downloads and fundraise with WordPress. Easily connect Paddle, Stripe, Paypal to accept donations and manage downloads.
 * Plugin URI:  https://wpsmartpay.com/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Tags: download manager, digital product, donation, ecommerce, paddle, stripe, paypal, document manager, file manager, download protection, recurring payment, donations, donation plugin, wordpress donation plugin, wp donation, fundraising, fundraiser, crowdfunding, wordpress donations, gutenberg, gutenberg donations, nonprofit, paypal donations, paypal donate, stripe donations, stripe donate, authorize.net, authorize.net donations, bkash, bkash payment,
 * Version:     2.6.6
 * Author:      WPSmartPay
 * Author URI:  https://wpsmartpay.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
 * Text Domain: smartpay
 * Domain Path: resources/languages
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

define('SMARTPAY_VERSION', '2.6.6');
define('SMARTPAY_PLUGIN_FILE', __FILE__);
define('SMARTPAY_PLUGIN_ASSETS', plugins_url('public', __FILE__));
define('SMARTPAY_STORE_URL', 'https://wpsmartpay.com/');

// Create The Application
$app = require __DIR__ . '/bootstrap.php';

global $smartpay_options;

$smartpay_options = smartpay_get_settings();

add_action('plugins_loaded', function () use ($app) {
    do_action('smartpay_loaded');

    load_plugin_textdomain('smartpay', false, dirname(plugin_basename(__FILE__)) . '/resources/languages');

    // Run The Application
    $app->boot();
});

add_action('init', function () {
    do_action('smartpay_init');
});
