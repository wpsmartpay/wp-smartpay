<?php

namespace SmartPay\Modules\Frontend;
defined('ABSPATH') || exit;

use SmartPay\Modules\Frontend\Utilities\Downloader;
use SmartPay\Http\Controllers\Rest\CustomerController;
use WP_REST_Server;

class Common
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->make(Downloader::class);

        $this->app->addAction('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function enqueueScripts()
    {
        // Load the frontend bundle only on pages that actually render SmartPay
        // content. Loading it site-wide trips wp.org's EnqueuedScriptsScope /
        // EnqueuedStylesScope checks and wastes bandwidth everywhere else.
        if (! $this->pageNeedsAssets()) {
            return;
        }

        wp_register_style('smartpay-app', SMARTPAY_PLUGIN_ASSETS . '/css/app.css', '', SMARTPAY_VERSION);
        wp_enqueue_style('smartpay-app');

        wp_register_script('smartpay-bootstrap', SMARTPAY_PLUGIN_ASSETS . '/js/bootstrap.js', ['jquery'], SMARTPAY_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
        wp_enqueue_script('smartpay-bootstrap');
        // app.js bundles modules that import @wordpress/i18n (externalized to
        // window.wp.i18n). Without wp-i18n, `wp` is undefined and the bundle
        // throws at eval — silently killing every handler bound after that point
        // (e.g. the coupon toggle/apply). Declaring it as a dependency loads it.
        wp_register_script('smartpay-app', SMARTPAY_PLUGIN_ASSETS . '/js/app.js', ['jquery', 'smartpay-bootstrap', 'wp-i18n'], SMARTPAY_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
        wp_enqueue_script('smartpay-app');
        wp_localize_script(
            'smartpay-app',
            'smartpay',
            array(
                'restUrl'  => get_rest_url('', 'smartpay'),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'apiNonce' => wp_create_nonce('wp_rest'),

                'options' => $this->getOptionsScriptsData(),
            )
        );
    }

    /**
     * Whether the current request renders SmartPay content and therefore needs
     * the frontend bundle. Detects legacy shortcodes and Gutenberg blocks on the
     * queried singular post. Filterable so Pro / edge cases (widgets, popups,
     * archive templates) can force the assets to load.
     *
     * @return bool
     */
    protected function pageNeedsAssets(): bool
    {
        $needs = false;
        $post  = get_queried_object();

        if ($post instanceof \WP_Post) {
            $content = (string) $post->post_content;

            $shortcodes = [
                'smartpay_dashboard',
                'smartpay_form',
                'smartpay_payment_receipt',
                'smartpay_product',
                'smartpay_user_login',
                'smartpay_user_profile',
                'smartpay_user_registration',
                'sp_form',
            ];
            foreach ($shortcodes as $tag) {
                if (has_shortcode($content, $tag)) {
                    $needs = true;
                    break;
                }
            }

            if (! $needs) {
                $blocks = ['smartpay/form', 'smartpay/pricing', 'smartpay/product'];
                foreach ($blocks as $block) {
                    if (has_block($block, $post)) {
                        $needs = true;
                        break;
                    }
                }
            }
        }

        return (bool) apply_filters('smartpay_needs_frontend_assets', $needs, $post);
    }

    public function registerRestRoutes()
    {
        $customerController = $this->app->make(CustomerController::class);

        register_rest_route('smartpay/v1/public', 'customers/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$customerController, 'show'],
                'permission_callback' => [$customerController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$customerController, 'update'],
                'permission_callback' => [$customerController, 'middleware'],
            ],
        ]);
    }

    /**
     * Get options data for localize scripts
     *
     * @return array
     */
    protected function getOptionsScriptsData(): array
    {
        // global $smartpay_options;

        return [
            'currency'          => smartpay_get_currency(),
            'currencySymbol'    => smartpay_get_currency_symbol(),
            'isTestMode'        => smartpay_is_test_mode(),
        ];
    }
}
