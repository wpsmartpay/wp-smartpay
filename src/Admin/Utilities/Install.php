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
     * @since 0.0.2
     * @access private
     */
    private static $instance = null;

    /**
     * The installed plugin version
     * @since 0.0.2
     * @access private
     */
    private $installed_version = '';

    /**
     * Construct Install class.
     *
     * @since 0.0.2
     * @access private
     */
    private function __construct()
    {
        $this->installed_version = get_option('smartpay_version');

        register_activation_hook(SMARTPAY_FILE, [$this, 'activate']);
    }

    /**
     * Main Install Instance.
     *
     * Ensures that only one instance of Install exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.2
     * @return object|Install
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Install)) {
            self::$instance = new self();
            self::$instance->upload = Upload::instance();
        }

        return self::$instance;
    }

    /**
     * Plugin activate.
     *
     * @since 0.0.2
     * @access public
     * @return void
     */
    public function activate()
    {
        $installed = get_option('wp_smartpay_installed');

        // If not installed then run installation process
        if (!$installed) {
            $this->_install();
        } else if (-1 === version_compare($this->installed_version, SMARTPAY_VERSION)) {
            $this->_upgrade();
        }
    }

    /**
     * Install SmartPay.
     *
     * @since 0.0.2
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

        // Protect upload directory
        self::$instance->upload->protect_upload_directory(true);
    }

    /**
     * Upgrade SmartPay.
     *
     * @since 0.0.2
     * @access private
     * @return void
     */
    private function _upgrade()
    {
        if (-1 === version_compare($this->installed_version, 'x.x.x')) {
            //
        } else if (-1 === version_compare($this->installed_version, 'x.x.x')) {
            //
        }
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

        // Setup customer dashboard page
        $customer_dashboard_page = array_key_exists('customer_dashboard_page', $smartpay_settings) ? $smartpay_settings['customer_dashboard_page'] : false;

        if (empty($customer_dashboard_page)) {
            $customer_dashboard_page = \wp_insert_post(
                array(
                    'post_title'     => __('Dashboard', 'smartpay'),
                    'post_name'      => 'smartpay-customer-dashboard',
                    'post_content'   => sprintf("<!-- wp:shortcode -->%s<!-- /wp:shortcode -->", '[smartpay_dashboard]'),
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                )
            );
        }

        $options = array(
            'payment_page'            => $payment_page,
            'payment_success_page'    => $payment_success_page,
            'payment_failure_page'    => $payment_failure_page,
            'customer_dashboard_page' => $customer_dashboard_page,
        );

        update_option('smartpay_settings', array_merge($smartpay_settings, $options));
    }

    /**
     * Set default settings.
     *
     * @since 0.0.2
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
