<?php

/**
 * Plugin Name: SmartPay
 * Description: Simplest way to sell digital downloads and fundraise with WordPress. Easily connect Paddle, Stripe, Paypal to accept donations and manage downloads.
 * Plugin URI:  https://wpsmartpay.com/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Tags: download manager, digital product, donation, ecommerce, paddle, stripe, paypal, document manager, file manager, download protection, recurring payment, donations, donation plugin, wordpress donation plugin, wp donation, fundraising, fundraiser, crowdfunding, wordpress donations, gutenberg, gutenberg donations, nonprofit, paypal donations, paypal donate, stripe donations, stripe donate, authorize.net, authorize.net donations, bkash, bkash payment,
 * Version:     1.0.0-beta.1
 * Author:      WPSmartPay
 * Author URI:  https://wpsmartpay.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
 * Text Domain: smartpay
 * Domain Path: languages
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

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Includes vendor files.
require_once __DIR__ . '/vendor/autoload.php';

// Define the necessary constants.
if (!defined('SMARTPAY_PLUGIN_FILE')) {
    define('SMARTPAY_PLUGIN_FILE', __FILE__);
}

// Initialize SmartPay.
function SmartPay()
{
    return SmartPay\SmartPay::instance();
}
SmartPay();