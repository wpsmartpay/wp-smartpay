<?php

namespace SmartPay\Modules\Admin;
defined('ABSPATH') || exit;

use SmartPay\Models\Payment;
use SmartPay\Modules\Admin\Logger;
use SmartPay\Modules\Admin\Report;
use SmartPay\Modules\Admin\Setting;

class Admin
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->build(Setting::class);

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);
        $this->app->addAction('admin_menu', [$this, 'adminMenu']);
        $this->app->addAction('admin_bar_menu', [$this, 'adminToolbarMenu'], 999);
        $this->app->addAction('rest_api_init', [$this, 'registerAdminRestRoutes']);
        $this->app->addFilter('admin_footer_text', [$this, 'adminFooterText']);
        $this->app->addFilter('update_footer', [$this, 'adminFooterVersion'], 11);
    }

    public function adminMenu()
    {
        add_menu_page(
            __('WPSmartPay', 'smartpay'),
            __('WPSmartPay', 'smartpay'),
            'manage_options',
            'smartpay',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            },
            smartpay_svg_icon(),
            30
        );

        // Force hook prefix back to 'smartpay' — WordPress derives it from sanitize_title(menu_title)
        // which would give 'wpsmartpay'. We keep the slug-based prefix for stability.
        global $admin_page_hooks;
        $admin_page_hooks['smartpay'] = 'smartpay';

        add_submenu_page(
            'smartpay',
            __('Dashboard', 'smartpay'),
            __('Dashboard', 'smartpay'),
            'manage_options',
            'smartpay',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        if ( in_array( 'products', smartpay_get_activated_integrations(), true ) ) {
            add_submenu_page(
                'smartpay',
                __('WPSmartPay - Products', 'smartpay'),
                __('Products', 'smartpay'),
                'manage_options',
                'smartpay#/products',
                function () {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                    echo smartpay_view('admin');
                }
            );
        }

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Forms', 'smartpay'),
            __('Forms', 'smartpay'),
            'manage_options',
            'smartpay#/native-forms',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        if ( in_array( 'legacy_forms', smartpay_get_activated_integrations(), true ) ) {
            add_submenu_page(
                'smartpay',
                __('WPSmartPay - Forms (Legacy)', 'smartpay'),
                __('Forms (Legacy)', 'smartpay'),
                'manage_options',
                'smartpay-form',
                function () {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                    echo smartpay_view('form-builder');
                }
            );
        }

        do_action('smartpay_admin_add_menu_items');

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Invoices', 'smartpay'),
            __('Invoices', 'smartpay'),
            'manage_options',
            'smartpay#/invoices',
            function () {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Payments', 'smartpay'),
            __('Payments', 'smartpay'),
            'manage_options',
            'smartpay#/payments',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Subscriptions', 'smartpay'),
            __('Subscriptions', 'smartpay'),
            'manage_options',
            'smartpay#/subscriptions',
            function () {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Customers', 'smartpay'),
            __('Customers', 'smartpay'),
            'manage_options',
            'smartpay#/customers',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Coupons', 'smartpay'),
            __('Coupons', 'smartpay'),
            'manage_options',
            'smartpay#/coupons',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Reports', 'smartpay'),
            __('Reports', 'smartpay'),
            'manage_options',
            'smartpay#/reports',
            function () {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Integrations', 'smartpay'),
            __('Integrations', 'smartpay'),
            'manage_options',
            'smartpay-integrations',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('integrations');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Settings', 'smartpay'),
            __('Settings', 'smartpay'),
            'manage_options',
            'smartpay-setting',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('settings');
            }
        );

        add_submenu_page(
            'smartpay',
            __('WPSmartPay - Support', 'smartpay'),
            __('Support', 'smartpay'),
            'manage_options',
            'smartpay-support',
            function () {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo smartpay_view('support');
            }
        );

        $this->smartpayProMenu();

    }

    /**
     * Add SmartPay entry to the WP admin toolbar.
     *
     * @param \WP_Admin_Bar $wp_admin_bar
     */
    public function adminToolbarMenu( \WP_Admin_Bar $wp_admin_bar )
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Credit-card SVG, sized for the toolbar (20 × 20 px).
        $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"'
            . ' style="position:relative;top:4px;margin-right:5px;fill:currentColor;" aria-hidden="true">'
            . '<path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6'
            . 'c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>'
            . '</svg>';

        $wp_admin_bar->add_node(
            array(
                'id'    => 'smartpay-toolbar',
                'title' => $icon . esc_html__( 'WPSmartPay', 'smartpay' ),
                'href'  => esc_url( admin_url( 'admin.php?page=smartpay' ) ),
                'meta'  => array( 'class' => 'smartpay-toolbar-menu' ),
            )
        );

        $wp_admin_bar->add_node(
            array(
                'parent' => 'smartpay-toolbar',
                'id'     => 'smartpay-toolbar-dashboard',
                'title'  => esc_html__( 'Dashboard', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay' ) ),
            )
        );

        $wp_admin_bar->add_node(
            array(
                'parent' => 'smartpay-toolbar',
                'id'     => 'smartpay-toolbar-forms',
                'title'  => esc_html__( 'Forms', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay#/native-forms' ) ),
            )
        );

        $wp_admin_bar->add_node(
            array(
                'parent' => 'smartpay-toolbar',
                'id'     => 'smartpay-toolbar-payments',
                'title'  => esc_html__( 'Payments', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay#/payments' ) ),
            )
        );

        $wp_admin_bar->add_node(
            array(
                'parent' => 'smartpay-toolbar',
                'id'     => 'smartpay-toolbar-integrations',
                'title'  => esc_html__( 'Integrations', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay-integrations' ) ),
            )
        );

        $wp_admin_bar->add_node(
            array(
                'parent' => 'smartpay-toolbar',
                'id'     => 'smartpay-toolbar-settings',
                'title'  => esc_html__( 'Settings', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay-setting' ) ),
            )
        );
    }

    private function smartpayProMenu()
    {
        // check if wp-smartpay-pro or smartpay-pro plugin is in activated plugin list
        if (
            !in_array('wp-smartpay-pro/smartpay-pro.php', get_option('active_plugins'))
            &&
            !in_array('smartpay-pro/smartpay-pro.php', get_option('active_plugins'))
        ) {
            global $submenu;

            $submenu['smartpay'][99] = [
                "⭐ Upgrade to pro",
                "manage_options",
                'https://wpsmartpay.com',
            ];
        }
    }

    public function adminScripts($hook)
    {
        // Fallback: hook suffix can vary (e.g. URL-encoded slug); also check request page param
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading page slug for asset enqueue routing, not processing form data
        $request_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $is_main_admin_page = in_array($request_page, ['smartpay', 'smartpay#/products', 'smartpay#/customers', 'smartpay#/coupons', 'smartpay#/payments', 'smartpay#/subscriptions', 'smartpay#/invoices', 'smartpay#/reports'], true);

        $admin_style_hooks = [
            'toplevel_page_smartpay',
            'smartpay_page_smartpay-form',
            'smartpay_page_smartpay-setting',
            'smartpay_page_smartpay-integrations',
            'smartpay_page_smartpay#/products',
            'smartpay_page_smartpay#/customers',
            'smartpay_page_smartpay#/coupons',
            'smartpay_page_smartpay#/payments',
            'smartpay_page_smartpay#/subscriptions',
            'smartpay_page_smartpay#/reports',
        ];
        if (in_array($hook, $admin_style_hooks, true) || $is_main_admin_page) {
            wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.css', '', SMARTPAY_VERSION);
            wp_register_style('smartpay-components', SMARTPAY_PLUGIN_ASSETS . '/css/components.css', '', SMARTPAY_VERSION);
            wp_enqueue_style('smartpay-admin'); // TODO: Remove admin css after refactoring
            wp_enqueue_style('smartpay-components');
            wp_enqueue_style('wp-components');
        }
        // Enqueue UI components on main admin SPA and form-builder (form list) page so WPSmartPayUI is defined
        $admin_spa_hooks = [
            'toplevel_page_smartpay',
            'smartpay_page_smartpay#/products',
            'smartpay_page_smartpay#/customers',
            'smartpay_page_smartpay#/coupons',
            'smartpay_page_smartpay#/payments',
            'smartpay_page_smartpay#/subscriptions',
            'smartpay_page_smartpay#/reports',
            'smartpay_page_smartpay-form',
        ];
        if (in_array($hook, $admin_spa_hooks, true) || $is_main_admin_page) {
            wp_register_script('smartpay-ui', SMARTPAY_PLUGIN_ASSETS . '/js/ui.js', ['wp-element', 'wp-data'], SMARTPAY_VERSION, true);
            wp_enqueue_script('smartpay-ui');
        }
        $main_admin_hooks = [
            'toplevel_page_smartpay',
            'smartpay_page_smartpay#/products',
            'smartpay_page_smartpay#/customers',
            'smartpay_page_smartpay#/coupons',
            'smartpay_page_smartpay#/payments',
            'smartpay_page_smartpay#/subscriptions',
            'smartpay_page_smartpay#/reports',
        ];
        if (in_array($hook, $main_admin_hooks, true) || $is_main_admin_page) {
            wp_register_script('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/js/admin.js', ['jquery', 'wp-element', 'wp-data', 'smartpay-ui'], SMARTPAY_VERSION, true);
            wp_enqueue_script('smartpay-admin');
            wp_localize_script(
                'smartpay-admin',
                'smartpay',
                array(
                    'restUrl'  => get_rest_url('', 'smartpay'),
                    'adminUrl'  => admin_url('admin.php'),
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'apiNonce' => wp_create_nonce('wp_rest'),
                    'options'    => $this->getOptionsScriptsData(),
					'logo'       => SMARTPAY_PLUGIN_ASSETS . '/img/logo-lockup-color.png',
					'pluginUrl'  => SMARTPAY_PLUGIN_ASSETS,
					'version'    => SMARTPAY_VERSION,
                )
            );

            // Canonical Pro state for locked-feature screens. Detected server-side so it
            // works even when Pro is installed but unlicensed (Pro's own JS does not load then).
            wp_localize_script(
                'smartpay-admin',
                'smartpayProData',
                array(
                    'isInstalled' => defined( 'SMARTPAY_PRO_VERSION' ),
                    'isActive'    => smartpay_is_pro_active(),
                    'licenseUrl'  => admin_url( 'admin.php?page=smartpay-setting&tab=licenses' ),
                )
            );

            // WARN: Enqueue to bottom
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        if ('smartpay_page_smartpay-setting' === $hook) {
            wp_enqueue_script('smartpay-debug-log', SMARTPAY_PLUGIN_ASSETS . '/js/debuglog.js', ['jquery'], SMARTPAY_VERSION, true);
            wp_localize_script(
                'smartpay-debug-log',
                'debugLog',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );
        }

        if ('smartpay_page_smartpay-support' === $hook) {
            wp_enqueue_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.css', array(), SMARTPAY_VERSION);
            wp_register_style('smartpay-components', SMARTPAY_PLUGIN_ASSETS . '/css/components.css', array(), SMARTPAY_VERSION);
            wp_enqueue_style('smartpay-components');
            wp_enqueue_style('wp-components');
            wp_enqueue_script(
                'smartpay-support',
                SMARTPAY_PLUGIN_ASSETS . '/js/support.js',
                array('wp-element', 'wp-i18n', 'wp-components'),
                SMARTPAY_VERSION,
                true
            );
            wp_localize_script('smartpay-support', 'smartpaySupport', $this->getSupportData());
            wp_localize_script( 'smartpay-support', 'smartpay', array( 'logo' => SMARTPAY_PLUGIN_ASSETS . '/img/logo-lockup-color.png' ) );
        }

        $this->registerBlocks($hook);
    }

    public function registerBlocks($hook)
    {
        // Exclude blocks from the form-builder
        if ('smartpay_page_smartpay-form' === $hook) {
            return;
        }

        // Global
        wp_enqueue_script('smartpay-editor-blocks', SMARTPAY_PLUGIN_ASSETS . '/blocks/index.js', ['wp-element', 'wp-plugins', 'wp-blocks', 'wp-block-editor', 'wp-data'], SMARTPAY_VERSION, false);

        // Product
        register_block_type('smartpay/product', array(
            'editor_script' => 'smartpay-editor-blocks',
        ));

        // Form
        register_block_type('smartpay/form', array(
            'editor_script' => 'smartpay-editor-blocks',
        ));

        wp_localize_script(
            'smartpay-editor-blocks',
            'smartpay',
            array(
                'restUrl'  => get_rest_url('', 'smartpay'),
                'adminUrl'  => admin_url('admin.php'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'apiNonce' => wp_create_nonce('wp_rest'),
            )
        );
    }


    /**
     * Get options data for localize scripts
     *
     * @return array
     */
    protected function getOptionsScriptsData(): array
    {
        $raw_gateways = apply_filters( 'smartpay_gateways', array() );
        $gateways     = array();
        foreach ( $raw_gateways as $slug => $gateway ) {
            $gateways[ $slug ] = $gateway['admin_label'] ?? $slug;
        }

        return [
            'currency'         => smartpay_get_currency(),
            'currencySymbol'   => smartpay_get_currency_symbol(),
            'isTestMode'       => smartpay_is_test_mode(),
            'currencies'       => smartpay_get_currencies(),
            'gateways'         => $gateways,
            'businessName'     => smartpay_get_option( 'business_name', '' ),
            'productsEnabled'  => in_array( 'products', smartpay_get_activated_integrations(), true ),
        ];
    }

    /**
     * Collect system info and debug log content for the support page.
     */
    private function getSupportData(): array
    {
        global $wpdb;

        $logger = new Logger();

        $active_plugins = array();
        foreach ( get_option( 'active_plugins', array() ) as $plugin_path ) {
            $data              = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
            $active_plugins[]  = array(
                'name'    => $data['Name'] ?? $plugin_path,
                'version' => $data['Version'] ?? '—',
                'url'     => $data['PluginURI'] ?? '',
            );
        }

        return array(
            'nonce'      => wp_create_nonce( 'wp_rest' ),
            'restUrl'    => get_rest_url( null, 'smartpay/v1' ),
            'logo'       => SMARTPAY_PLUGIN_ASSETS . '/img/logo-lockup-color.png',
            'version'    => SMARTPAY_VERSION,
            'debugLog'   => $logger->get_file_contents(),
            'systemInfo' => array(
                'wordpress' => array(
                    array( 'label' => 'Version',        'value' => get_bloginfo( 'version' ) ),
                    array( 'label' => 'Site URL',       'value' => get_site_url() ),
                    array( 'label' => 'Home URL',       'value' => get_home_url() ),
                    array( 'label' => 'Language',       'value' => get_locale() ),
                    array( 'label' => 'Multisite',      'value' => is_multisite() ? 'Yes' : 'No' ),
                    array( 'label' => 'Debug Mode',     'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Enabled' : 'Disabled' ),
                    array( 'label' => 'Memory Limit',   'value' => WP_MEMORY_LIMIT ),
                ),
                'server' => array(
                    array( 'label' => 'PHP Version',        'value' => PHP_VERSION ),
                    array( 'label' => 'MySQL Version',      'value' => $wpdb->db_version() ),
                    array( 'label' => 'Server Software',    'value' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown' ),
                    array( 'label' => 'PHP Memory Limit',   'value' => ini_get( 'memory_limit' ) ),
                    array( 'label' => 'Max Upload Size',    'value' => size_format( wp_max_upload_size() ) ),
                    array( 'label' => 'Max Execution Time', 'value' => ini_get( 'max_execution_time' ) . 's' ),
                    array( 'label' => 'cURL Version',       'value' => function_exists( 'curl_version' ) ? ( curl_version()['version'] ?? 'Available' ) : 'Not available' ),
                ),
                'smartpay' => array(
                    array( 'label' => 'WPSmartPay Version', 'value' => SMARTPAY_VERSION ),
                    array( 'label' => 'Active Gateway',   'value' => smartpay_get_default_gateway() ?: 'None' ),
                    array( 'label' => 'Test Mode',        'value' => smartpay_is_test_mode() ? 'Enabled' : 'Disabled' ),
                    array( 'label' => 'Currency',         'value' => smartpay_get_currency() ),
                ),
                'plugins' => $active_plugins,
            ),
        );
    }

    /**
     * Register admin REST routes (support tools + wizard setup).
     */
    public function registerAdminRestRoutes(): void
    {
        register_rest_route(
            'smartpay/v1',
            'support/debug-log/clear',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'restClearDebugLog' ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        register_rest_route(
            'smartpay/v1',
            'wizard/setup',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'restWizardSetup' ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );
    }

    public function restClearDebugLog(): \WP_REST_Response
    {
        $logger = new Logger();
        $logger->clear_log_file();

        $settings                       = get_option( 'smartpay_settings', array() );
        $settings['smartpay_debug_log'] = null;
        update_option( 'smartpay_settings', $settings, false );

        return new \WP_REST_Response( array( 'cleared' => true ) );
    }

    /**
     * Save wizard setup data (currency + business name) to smartpay_settings.
     *
     * @param \WP_REST_Request $request REST request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function restWizardSetup( \WP_REST_Request $request ) {
        $settings = get_option( 'smartpay_settings', array() );

        $currency = sanitize_text_field( $request->get_param( 'currency' ) ?? '' );
        if ( $currency ) {
            $valid_currencies = array_keys( smartpay_get_currencies() );
            if ( ! in_array( $currency, $valid_currencies, true ) ) {
                return new \WP_Error(
                    'invalid_currency',
                    esc_html__( 'The selected currency is not supported.', 'smartpay' ),
                    array( 'status' => 422 )
                );
            }
            $settings['currency'] = $currency;
        }

        $business_name = sanitize_text_field( $request->get_param( 'business_name' ) ?? '' );
        if ( '' !== $business_name ) {
            $settings['business_name'] = mb_substr( $business_name, 0, 200 );
        }

        update_option( 'smartpay_settings', $settings, false );

        return new \WP_REST_Response( array( 'saved' => true ) );
    }


    /**
     * Replace the default WP admin footer text on all SmartPay pages.
     *
     * @param string $text Default footer text.
     * @return string
     */
    public function adminFooterText( string $text ): string
    {
        if ( ! $this->isSmartPayAdminPage() ) {
            return $text;
        }

        $rate_url = 'https://wordpress.org/support/plugin/smartpay/reviews/#new-post';

        return sprintf(
            wp_kses(
                /* translators: %s: five-star rating link */
                __( 'If you like <strong>WPSmartPay</strong> please leave us a %s rating. A huge thanks in advance!', 'smartpay' ),
                [ 'strong' => [] ]
            ),
            '<a href="' . esc_url( $rate_url ) . '" target="_blank" rel="noopener noreferrer" style="color:#f0ad4e;text-decoration:none;" aria-label="' . esc_attr__( 'Rate WPSmartPay on WordPress.org', 'smartpay' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
        );
    }

    /**
     * Replace the WP version string in the admin footer on all SmartPay pages.
     *
     * @param string $text Default version text.
     * @return string
     */
    public function adminFooterVersion( string $text ): string
    {
        if ( ! $this->isSmartPayAdminPage() ) {
            return $text;
        }

        return sprintf(
            /* translators: %s: plugin version number */
            esc_html__( 'Version %s', 'smartpay' ),
            esc_html( SMARTPAY_VERSION )
        );
    }

    /**
     * Determine whether the current admin screen belongs to SmartPay.
     *
     * @return bool
     */
    private function isSmartPayAdminPage(): bool
    {
        $screen = get_current_screen();

        if ( ! $screen ) {
            return false;
        }

        // All SmartPay menu pages share 'smartpay' in the screen id.
        return str_contains( $screen->id, 'smartpay' );
    }
}
