<?php

namespace SmartPay\Admin\Integrations;

// Exit if accessed directly.
defined('ABSPATH') || exit;

final class Integrations
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    /**
     * Construct Integrations class.
     *
     * @since 0.0.1
     */
    private function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('wp_ajax_toggle_integration_activation', [$this, 'toggle_integration_activation']);
        add_action('wp_ajax_nopriv_toggle_integration_activation', [$this, 'toggle_integration_activation']);
    }

    /**
     * Main Integrations Instance.
     *
     * Ensures that only one instance of Integrations exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     *
     * @return object|Integrations
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Integrations)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function toggle_integration_activation()
    {
        if (!isset($_POST['payload']['nonce']) || !wp_verify_nonce($_POST['payload']['nonce'], 'smartpay_integrations_toggle_activation')) {
            echo 'Invlid request';
            die();
        }

        $action    = sanitize_text_field($_POST['payload']['action'] ?? '');
        $namespace = sanitize_text_field($_POST['payload']['namespace'] ?? '');

        if (!in_array($namespace, array_keys(smartpay_integrations()))) {
            echo 'Requested for invalid integration';
            die();
        }

        if ('activate' === $action) {
            $this->activate_integration($namespace);
        } else {
            $this->deactivate_integration($namespace);
        }

        die(); // Must terminate the api/ajax request
    }

    private function activate_integration(string $integration)
    {
        $settings = smartpay_get_settings();

        if (!is_array($settings['activated_integrations'])) {
            $settings['activated_integrations'] = [];
        }

        if (!in_array($integration, $settings['activated_integrations'])) {
            array_push($settings['activated_integrations'], $integration);
        }

        smartpay_update_settings($settings);
        echo 'Activated';
    }

    private function deactivate_integration(string $integration)
    {
        $settings = smartpay_get_settings();

        if (($key = array_search($integration, $settings['activated_integrations'])) >= 0) {
            unset($settings['activated_integrations'][$key]);
        }

        smartpay_update_settings($settings);
        echo 'Disabled';
    }

    public function enqueue_scripts()
    {
        wp_register_script('smartpay-integration', SMARTPAY_PLUGIN_ASSETS . '/js/integration.js', ['jquery'], SMARTPAY_VERSION);

        wp_enqueue_script('smartpay-integration');
    }
}