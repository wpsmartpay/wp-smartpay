<?php

/**
 * SmartPay
 *
 * Plugin Name: SmartPay
 * Plugin URI:  https://wpsmartpay.com/
 * Description: The most easiest way to sell digital downloads and get paid in WordPress.
 * Tags: digital downloads, ecommerce, paddle, stripe, paypal, payment forms
 * Version:     0.0.1
 * Author:      SmartPay Team
 * Author URI:  https://wpsmartpay.com/
 * Text Domain: smartpay
 *
 * Requires PHP: 7.0.0
 * Requires at least: 4.9
 * Tested up to: 5.4
 */

use SmartPay\Shortcode;
use SmartPay\Admin\Admin;
use SmartPay\Forms\Form;
use SmartPay\Gateways\Gateway;
use SmartPay\Products\Product;
use SmartPay\Payments\Payment;
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
    public $version = '0.0.1';

    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Database version key
     *
     * @var string
     * @since 0.0.1
     */
    private $db_version_key = 'smartpay_version';

    /**
     * Session Object.
     *
     * This holds purchase sessions, and anything else stored in the session.
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

        if (is_admin()) {
            Admin::instance();
        }

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

            self::$instance->session   = Session::instance();
            self::$instance->product   = Product::instance();
            self::$instance->gateway   = Gateway::instance();
            self::$instance->payment   = Payment::instance();
            self::$instance->form      = Form::instance();
            self::$instance->shortcode = Shortcode::instance();
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
     *
     * @param string $name
     * @param string|bool $value
     *
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
        register_activation_hook(__FILE__, [$this, 'activate']);

        // TODO: Implement deactivation hook
        // register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('plugins_loaded', array($this, 'on_plugins_loaded'), -1);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_smartpay_scripts']);
    }

    /**
     * Plugin activation hook.
     *
     * @since 0.0.1
     * @access public
     * @return void
     */
    public function activate()
    {
        $installed = get_option('wp_smartpay_installed');
        if (!$installed) {
            update_option('wp_smartpay_installed', time());
        }

        update_option($this->db_version_key, SMARTPAY_VERSION);

        self::create_pages();
    }

    /**
     * Create necessary pages.
     *
     * @since 0.0.1
     * @access public
     * @return void
     */
    public static function create_pages()
    {
        if (false == get_option('smartpay_settings')) {
            add_option('smartpay_settings');
        }

        $current_options = get_option('smartpay_settings', array());

        // Checks if the purchase page option exists
        $payment_page = array_key_exists('payment_page', $current_options) ? get_post($current_options['payment_page']) : false;
        if (empty($payment_page)) {
            // Checkout Page
            $payment_page = wp_insert_post(
                array(
                    'post_title'     => __('SmartPay Payment', 'smartpay'),
                    'post_name'      => 'smartpay-payment',
                    'post_content'   => '',
                    'post_status'    => 'publish',
                    'post_author'    => 1,
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                )
            );
        }

        $payment_page = isset($payment_page) ? $payment_page : $current_options['payment_page'];

        $payment_success_page = array_key_exists('payment_success_page', $current_options) ? get_post($current_options['payment_success_page']) : false;
        if (empty($payment_success_page)) {
            // Payment Confirmation (Success) Page
            $payment_success_page = wp_insert_post(
                array(
                    'post_title'     => __('Payment Confirmation', 'smartpay'),
                    'post_name' => 'smartpay-payment-confirmation',
                    'post_content'   => "<!-- wp:paragraph --><p>Thank you for your payment.</p><!-- /wp:paragraph --> <!-- wp:shortcode -->[smartpay_payment_receipt]<!-- /wp:shortcode -->",
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                )
            );
        }

        $payment_failure_page = array_key_exists('payment_failure_page_page', $current_options) ? get_post($current_options['payment_failure_page_page']) : false;
        if (empty($payment_failure_page)) {
            // Payment Confirmation (Success) Page
            $payment_failure_page = wp_insert_post(
                array(
                    'post_title'     => __('Payment Failed', 'smartpay'),
                    'post_name'      => 'smartpay-payment-failed',
                    'post_content'   => __('<!-- wp:paragraph --><p>We\'re sorry, but your transaction failed to process. Please try again or contact site support.</p><!-- /wp:paragraph -->', 'smartpay') . sprintf("<!-- wp:shortcode -->%s<!-- /wp:shortcode -->\n", '[smartpay_payment_error show_to="admin"]' . "\n"),
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                )
            );
        }

        $options = array(
            'payment_page'          => $payment_page,
            'payment_success_page'  => $payment_success_page,
            'payment_failure_page'  => $payment_failure_page,
            'gateways'              => ['paddle' => 1],
            'default_gateway'       => 'paddle'
        );

        update_option('smartpay_settings', $options);
    }

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