<?php

namespace SmartPay\Customers;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Customer
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Customer class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        // Process shortcode profile update
        add_action('init', [$this, 'process_shortcode_update_user_profile']);
    }

    /**
     * Main Customer Instance.
     *
     * Ensures that only one instance of Customer exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Customer
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Customer)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Update shortcode dashboard user profile.
     *
     * @since 0.0.1
     * @access public
     */
    public function process_shortcode_update_user_profile()
    {
        // TODO: Add flush message

        if (!isset($_POST['smartpay_process_profile_update']) || !wp_verify_nonce($_POST['smartpay_process_profile_update'], 'smartpay_process_profile_update')) {
            return;
        }

        if (!is_user_logged_in() || get_current_user_id() <= 0) return;

        extract(sanitize_post($_POST));

        if (empty($first_name) || empty($last_name) || empty($email)) return;

        if (isset($password) && (!isset($password_confirm) || $password !== $password_confirm)) 'Error.';

        $user_data = wp_update_user(array(
            'ID' => get_current_user_id(),
            'display_name' => $first_name  . ' ' . $last_name,
            'user_email' => $email,
        ));

        if (is_wp_error($user_data)) {
            echo 'Error.';
        } else {
            echo 'User profile updated.';
        }
    }

    /**
     * Create customer database table.
     *
     * @since 0.0.1
     * @access public
     */
    public static function create_db_table()
    {
        return (new DB_Customer)->create_table();
    }
}