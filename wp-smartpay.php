<?php

/**
 * SmartPay
 *
 * Plugin Name: SmartPay Core
 * Plugin URI:  https://wpsmartpay.com/
 * Description: The most easiest way to sell digital downloads and get paid in WordPress.
 * Tags: digital downloads, ecommerce, paddle, stripe, paypal, payment forms
 * Version:     0.1
 * Author:      SmartPay Team
 * Author URI:  https://wpsmartpay.com/
 * Text Domain: smartpay
 *
 * Requires PHP: 7.0.0
 * Requires at least: 4.9
 * Tested up to: 5.4
 */

namespace SmartPay;

use SmartPay\Admin\Admin;

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
	 * Session Object.
	 *
	 * This holds purchase sessions, and anything else stored in the session.
	 *
	 * @var object|SP_Session
	 * @since 1.5
	 */
	public $session;

	/**
	 * Construct SmartPay class.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function __construct()
	{

		if (!session_id()) {
			session_start();
		}

		global $smartpay_options;
		$smartpay_options = Setting::get_settings();

		PostType::instance();

		Shortcode::instance();

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
		if (!defined('SMARTPAY_VERSION')) {
			define('SMARTPAY_VERSION', '0.1');
		}

		// Define plugin name.
		if (!defined('SMARTPAY_PLUGIN_NAME')) {
			define('SMARTPAY_PLUGIN_NAME', 'WP SmartPay');
		}

		// Define plugin main file.
		if (!defined('SMARTPAY_FILE')) {
			define('SMARTPAY_FILE', __FILE__);
		}

		// Plugin Folder URL.
		if (!defined('SMARTPAY_PLUGIN_URL')) {
			define('SMARTPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
		}

		// Define plugin.
		if (!defined('SMARTPAY_PLUGIN_DIR')) {
			define('SMARTPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
		}

		// Define plugin store URL.
		if (!defined('SMARTPAY_STORE_URL')) {
			define('SMARTPAY_STORE_URL', 'https://wpsmartpay.com/');
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
		add_action('wp_enqueue_scripts', [$this, 'enqueue_smartpay_scripts']);

		register_activation_hook(__FILE__, [$this, 'activate']);

		// register_deactivation_hook(__FILE__, [$this, 'deactivate']);
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

		update_option('smartpay_version', SMARTPAY_VERSION);

		self::create_pages();
	}

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
					'post_title'     => __('SmartPay Payment', 'wp-smartpay'),
					'post_name' => 'smartpay-payment',
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
					'post_title'     => __('Payment Confirmation', 'wp-smartpay'),
					'post_name' => 'smartpay-payment-confirmation',
					'post_content'   => "<!-- wp:paragraph --><p>Thank you for your payment.</p><!-- /wp:paragraph --> <!-- wp:shortcode -->[smartpay_payment_receipt]<!-- /wp:shortcode -->",
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);
		}

		$payment_failure_page = array_key_exists('payment_failure_page', $current_options) ? get_post($current_options['payment_failure_page']) : false;
		if (empty($payment_failure_page)) {
			// Payment Confirmation (Success) Page
			$payment_failure_page = wp_insert_post(
				array(
					'post_title'     => __('Payment Failed', 'wp-smartpay'),
					'post_name' => 'smartpay-payment-failed',
					'post_content'   => __('<!-- wp:paragraph --><p>We\'re sorry, but your transaction failed to process. Please try again or contact site support.</p><!-- /wp:paragraph -->', 'wp-smartpay') . sprintf("<!-- wp:shortcode -->%s<!-- /wp:shortcode -->\n", '[smartpay_payment_error show_to="admin"]' . "\n"),
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
			'gateways'                => ['paddle' => 1],
			'default_gateway'       => 'paddle'
		);

		update_option('smartpay_settings', $options);
	}

	public function enqueue_smartpay_scripts()
	{
		// Styles
		wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_URL . '/assets/css/admin.min.css', '', SMARTPAY_VERSION);

		wp_enqueue_style('smartpay-admin');

		// Scripts
		wp_register_script('smartpay-app', SMARTPAY_PLUGIN_URL . '/assets/js/app.js', ['jquery'], SMARTPAY_VERSION, true);

		// wp_enqueue_script('jquery');
		wp_enqueue_script('smartpay-admin');
	}
}

// Initialize SmartPay.
function SmartPay()
{
	return SmartPay::instance();
}
SmartPay();