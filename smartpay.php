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

use SmartPay\Shortcode;
use SmartPay\Admin\Admin;
use SmartPay\Customers\Customer;
use SmartPay\Emails\Email;
use SmartPay\Forms\Form;
use SmartPay\Gateways\Gateway;
use SmartPay\Products\Product;
use SmartPay\Payments\Payment;
use SmartPay\Products\Process_Download;
use SmartPay\Session;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Includes vendor files.
require_once __DIR__ . '/vendor/autoload.php';

final class SmartPay
{
    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0-beta.1';

    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Session Object.
     *
     * This holds sessions, and anything else stored in the session.
     *
     * @var object|SmartPay_Session
     * @since 0.0.1
     */
    public $session;

    /**
     * Construct SmartPay class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        global $smartpay_options;

        $smartpay_options = smartpay_get_settings();

        // Define constants.
        $this->define_constants();

        // Initialize actions.
        $this->init_actions();
    }

    /**
     * Main SmartPay Instance.
     *
     * Ensures that only one instance of SmartPay exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|SmartPay
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof SmartPay)) {

            self::$instance = new self();
            self::$instance->session            = Session::instance();
            self::$instance->product            = Product::instance();
            self::$instance->form               = Form::instance();
            self::$instance->gateway            = Gateway::instance();
            self::$instance->customer           = Customer::instance();
            self::$instance->payment            = Payment::instance();
            self::$instance->shortcode          = Shortcode::instance();
            self::$instance->email              = Email::instance();
            self::$instance->process_download   = Process_Download::instance();

            if (is_admin()) {
                self::$instance->admin = Admin::instance();
            }
        }

        return self::$instance;
    }

    /**
     * Define the necessary constants.
     *
     * @since 0.0.1
     * @access private
     * @return void
     */
    private function define_constants()
    {
        $this->define('SMARTPAY_VERSION', $this->version);
        $this->define('SMARTPAY_FILE', __FILE__);
        $this->define('SMARTPAY_DIR', dirname(__FILE__));
        $this->define('SMARTPAY_INC_DIR', dirname(__FILE__) . '/includes');
        $this->define('SMARTPAY_PLUGIN_ASSETS', plugins_url('assets', __FILE__));
        $this->define('SMARTPAY_STORE_URL', 'https://wpsmartpay.com/');
    }

    /**
     * Define constant if not already defined
     *
     * @since 0.0.1
     * @param string $name
     * @param string|bool $value
     * @return void
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Initialize actions.
     *
     * @since 0.0.1
     * @access private
     * @return void
     */
    private function init_actions()
    {
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'), -1);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_smartpay_scripts']);
    }

    /**
     * Action for another plugin.
     *
     * @since 0.0.1
     * @access public
     * @return void
     */
    public function on_plugins_loaded()
    {
        do_action('smartpay_loaded');
    }

    /**
     * Enqueue smartpay scripts.
     *
     * @since 0.0.1
     * @access public
     * @return void
     */
    public function enqueue_smartpay_scripts()
    {
        // Register scripts
        wp_register_style('smartpay-site', SMARTPAY_PLUGIN_ASSETS . '/css/site.min.css', '', SMARTPAY_VERSION);
        wp_register_script('smartpay-bootstrap', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/bootstrap.js', ['jquery'], SMARTPAY_VERSION);
        wp_register_script('smartpay-site', SMARTPAY_PLUGIN_ASSETS . '/js/site.js', '', SMARTPAY_VERSION, true);
        wp_register_script('smartpay-icons', SMARTPAY_PLUGIN_ASSETS . '/js/vendor/feather.min.js', ['smartpay-site'], SMARTPAY_VERSION, true);

        // Enqueue them
        wp_enqueue_style('smartpay-site');
        wp_enqueue_script('smartpay-bootstrap');
        wp_enqueue_script('smartpay-site');
        wp_enqueue_script('smartpay-icons');
        wp_add_inline_script('smartpay-icons', 'feather.replace()');
    }
}

// Initialize SmartPay.
function SmartPay()
{
    return SmartPay::instance();
}
SmartPay();