<?php

namespace ThemesGrove\SmartPay\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class AdminNotices
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct AdminNotices class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_notices', [$this, 'show_notices']);
        add_action('smartpay_dismiss_notices', [$this, 'dismiss_notices']);
    }

    /**
     * Main AdminNotices Instance.
     *
     * Ensures that only one instance of AdminNotices exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|AdminNotices
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof AdminNotices)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Show relevant notices
     *
     * @since 2.3
     */
    public function show_notices()
    {
        $notices = array(
            'updated' => array(),
            'error'   => array(),
        );

        if (!count(smartpay_get_enabled_payment_gateways(true))) {
            ob_start();
?>
<div class="error">
    <p><?php printf(__('No active payment gateway found. You must enable a payment gateway to proceed a payment. Visit <a href="%s">Settings</a> to set one.', 'wp-smartpay'), admin_url('admin.php?page=smartpay-setting&tab=gateways')); ?>
    </p>
</div>
<?php
            echo ob_get_clean();
        }

        // Global (non-action-based) messages
        if ((smartpay_get_option('payment_page', '') == '' || 'trash' == get_post_status(smartpay_get_option('payment_page', ''))) && current_user_can('edit_pages') && !get_user_meta(get_current_user_id(), '_smartpay_set_checkout_dismissed')) {
            ob_start();
        ?>
<div class="error">
    <p><?php printf(__('No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'wp-smartpay'), admin_url('admin.php?page=smartpay-setting')); ?>
    </p>
    <p><a
            href="<?php echo esc_url(add_query_arg(array('smartpay_action' => 'dismiss_notices', 'smartpay_notice' => 'set_checkout'))); ?>"><?php _e('Dismiss Notice', 'wp-smartpay'); ?></a>
    </p>
</div>
<?php
            echo ob_get_clean();
        }
        if (isset($_GET['post_type']) && 'smartpay_payment' == $_GET['post_type'] && current_user_can('view_smartpay_reports') && smartpay_is_test_mode()) {
            $notices['updated']['smartpay-payment-history-test-mode'] = sprintf(__('Note: Test Mode is enabled. While in test mode no live transactions are processed. <a href="%s">Settings</a>.', 'wp-smartpay'), admin_url('admin.php?page=smartpay-setting&tab=gateways'));
        }

        if (isset($_GET['smartpay-message'])) {
            // Shop reports errors
            if (current_user_can('view_smartpay_reports')) {
                switch ($_GET['smartpay-message']) {
                    case 'payment_deleted':
                        $notices['updated']['smartpay-payment-deleted'] = __('The payment has been deleted.', 'wp-smartpay');
                        break;
                    case 'email_sent':
                        $notices['updated']['smartpay-payment-sent'] = __('The purchase receipt has been resent.', 'wp-smartpay');
                        break;
                    case 'refreshed-reports':
                        $notices['updated']['smartpay-refreshed-reports'] = __('The reports have been refreshed.', 'wp-smartpay');
                        break;
                    case 'payment-note-deleted':
                        $notices['updated']['smartpay-payment-note-deleted'] = __('The payment note has been deleted.', 'wp-smartpay');
                        break;
                }
            }

            // Shop payments errors
            if (current_user_can('edit_smartpay_payments')) {
                switch ($_GET['smartpay-message']) {
                    case 'note-added':
                        $notices['updated']['smartpay-note-added'] = __('The payment note has been added successfully.', 'wp-smartpay');
                        break;
                    case 'payment-updated':
                        $notices['updated']['smartpay-payment-updated'] = __('The payment has been successfully updated.', 'wp-smartpay');
                        break;
                }
            }
        }

        if (count($notices['updated']) > 0) {
            foreach ($notices['updated'] as $notice => $message) {
                add_settings_error('smartpay-notices', $notice, $message, 'updated');
            }
        }

        if (count($notices['error']) > 0) {
            foreach ($notices['error'] as $notice => $message) {
                add_settings_error('smartpay-notices', $notice, $message, 'error');
            }
        }

        settings_errors('smartpay-notices');
    }

    /**
     * Dismiss admin notices when Dismiss links are clicked
     *
     * @since 2.3
     * @return void
     */
    function dismiss_notices()
    {
        if (isset($_GET['smartpay_notice'])) {
            update_user_meta(get_current_user_id(), '_smartpay_' . $_GET['smartpay_notice'] . '_dismissed', 1);
            wp_redirect(remove_query_arg(array('smartpay_action', 'smartpay_notice')));
            exit;
        }
    }
}