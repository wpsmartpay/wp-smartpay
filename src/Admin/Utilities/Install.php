<?php

namespace SmartPay\Admin\Utilities;

use SmartPay\Customers\Customer;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Install
{
    /**
     * The single instance of this class
     * @since x.x.x
     * @access private
     */
    private static $instance = null;

    /**
     * Construct Install class.
     *
     * @since x.x.x
     * @access private
     */
    private function __construct()
    {
        register_activation_hook(SMARTPAY_FILE, [$this, 'activate']);
    }

    /**
     * Main Install Instance.
     *
     * Ensures that only one instance of Install exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since x.x.x
     * @return object|Install
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Install)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Plugin activate.
     *
     * @since x.x.x
     * @access public
     * @return void
     */
    public function activate()
    {
        $installed = get_option('wp_smartpay_installed');

        // If not installed then run installation process
        if (!$installed) {
            $this->_install();
        }
    }

    /**
     * Install SmartPay.
     *
     * @since x.x.x
     * @access private
     * @return void
     */
    private function _install()
    {
        // Set installation time
        update_option('wp_smartpay_installed', time());

        // Set plugin version
        update_option('smartpay_version', SMARTPAY_VERSION);

        // Create smartpay_settings option
        if (false == get_option('smartpay_settings')) add_option('smartpay_settings');

        // Create necessary pages
        $this->_create_pages();

        // Create DB tables
        Customer::create_db_table();

        // Set default settings
        $this->_set_default_settings();
    }

    /**
     * Create necessary pages.
     *
     * @since 0.0.1
     * @access private
     * @return void
     */
    private function _create_pages()
    {
        $smartpay_settings = get_option('smartpay_settings', []);

        // Setup payment page
        $payment_page = array_key_exists('payment_page', $smartpay_settings) ? $smartpay_settings['payment_page'] : false;

        if (empty($payment_page)) {
            $payment_page = \wp_insert_post(
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

        // Setup payment success page
        $payment_success_page = array_key_exists('payment_success_page', $smartpay_settings) ? $smartpay_settings['payment_success_page'] : false;

        if (empty($payment_success_page)) {
            $payment_success_page = \wp_insert_post(
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

        // Setup payment failure page
        $payment_failure_page = array_key_exists('payment_failure_page', $smartpay_settings) ? $smartpay_settings['payment_failure_page'] : false;

        if (empty($payment_failure_page)) {
            $payment_failure_page = \wp_insert_post(
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

        // Setup payment history page
        $payment_history_page = array_key_exists('payment_history_page', $smartpay_settings) ? $smartpay_settings['payment_history_page'] : false;

        if (empty($payment_history_page)) {
            $payment_history_page = \wp_insert_post(
                array(
                    'post_title'     => __('Payment History', 'smartpay'),
                    'post_name'      => 'smartpay-payment-history',
                    'post_content'   => sprintf("<!-- wp:shortcode -->%s<!-- /wp:shortcode -->", '[smartpay_payment_history]'),
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                )
            );
        }

        // Setup customer account page
        $customer_account_page = array_key_exists('customer_account_page', $smartpay_settings) ? $smartpay_settings['customer_account_page'] : false;

        if (empty($customer_account_page)) {
            $customer_account_page = \wp_insert_post(
                array(
                    'post_title'     => __('Account', 'smartpay'),
                    'post_name'      => 'smartpay-customer-account',
                    'post_content'   => sprintf("<!-- wp:shortcode -->%s<!-- /wp:shortcode -->", '[smartpay_account]'),
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
            'payment_history_page'  => $payment_history_page,
            'customer_account_page' => $customer_account_page,
        );

        update_option('smartpay_settings', array_merge($smartpay_settings, $options));
    }

    /**
     * Set default settings.
     *
     * @since x.x.x
     * @access private
     * @return void
     */
    private function _set_default_settings()
    {
        $smartpay_settings = get_option('smartpay_settings', []);

        $options = array(
            // Gateway
            'gateways'              => ['paddle' => 1],
            'default_gateway'       => 'paddle',

            // Email
            'form_name'             => get_bloginfo('name'),
            'form_email'            => get_bloginfo('admin_email'),
        );

        update_option('smartpay_settings', array_merge($smartpay_settings, $options));
    }
}
