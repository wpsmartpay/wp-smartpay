<?php

/**
 * WP SmartPay
 *
 * Plugin Name: WP SmartPay
 * Plugin URI:  https://wpsmartpay.com/
 * Description:
 * Tags: paddle
 * Version:     0.1
 * Author:      WPSmartPay
 * Author URI:  https://wpsmartpay.com/
 * Text Domain: wp-smartpay
 *
 * Requires PHP: 7.0.0
 * Requires at least: 4.9
 * Tested up to: 5.4
 */

namespace ThemesGrove\SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Includes vendor files.
require_once __DIR__ . '/vendor/autoload.php';

final class SmartPay
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct SmartPay class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
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
     * @since 0.1
     * @return object|SmartPay
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof SmartPay)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define the necessary constants.
     *
     * @since 0.1
     * @access private
     * @return void
     */
    private function define_constants()
    {
        // Set plugin version.
        if (!defined('WP_SMARTPAY_VERSION')) {
            define('WP_SMARTPAY_VERSION', '0.1');
        }

        // Define plugin name.
        if (!defined('WP_SMARTPAY_PLUGIN_NAME')) {
            define('WP_SMARTPAY_PLUGIN_NAME', 'WP SmartPay');
        }

        // Define plugin main file.
        if (!defined('WP_SMARTPAY_FILE')) {
            define('WP_SMARTPAY_FILE', __FILE__);
        }

        // Plugin Folder URL.
        if (!defined('WP_SMARTPAY_URL')) {
            define('WP_SMARTPAY_URL', plugin_dir_url(__FILE__));
        }

        // Define plugin.
        if (!defined('WP_SMARTPAY_PATH')) {
            define('WP_SMARTPAY_PATH', plugin_dir_path(__FILE__));
        }

        // Define plugin store URL.
        if (!defined('WP_SMARTPAY_STORE_URL')) {
            define('WP_SMARTPAY_STORE_URL', 'https://themesgrove.com/');
        }
    }

    /**
     * Initialize actions.
     *
     * @since 0.1
     * @access private
     * @return void
     */
    private function init_actions()
    {
        global $smartpay_options;

        $smartpay_options = Setting::get_settings();

        Setting::instance();

        PostType::instance();

        Gateway::instance();

        Admin::instance();

        Shortcode::instance();

        Payment::instance();

        // add_action('admin_enqueue_scripts', [$this, 'enqueue_smartpay_styles']);

        register_activation_hook(__FILE__, [$this, 'activate']);
    }

    /**
     * Activate plugin.
     *
     * @since 0.1
     * @access public
     * @return void
     */
    public function activate()
    {
        $installed = get_option('wp_smartpay_installed');
        if (!$installed) {
            update_option('wp_smartpay_installed', time());
        }

        update_option('wp_smartpay_version', WP_SMARTPAY_VERSION);
    }

    // public function enqueue_smartpay_styles()
    // {
    //     wp_enqueue_style('app-css', plugins_url('/assets/css/app.css', __FILE__));
    //     // wp_enqueue_style('bootstrap-css', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');

    //     wp_enqueue_script('app-js', plugins_url('/assets/js/app.js', __FILE__), '', true);
    // }
}

/**
 * Initialize SmartPay.
 */
function wp_smartpay()
{
    return SmartPay::instance();
}
wp_smartpay();