<?php

namespace SmartPay\Modules\Admin;

use SmartPay\Models\Form;
use SmartPay\Models\Payment;
use SmartPay\Models\Product;
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
        $this->app->addAction('wp_ajax_smartpay_debug_log_clear', [$this, 'smartpayDebugLogClear']);
        // $this->app->addAction('smartpay_admin_add_menu_items', [$this, 'registerDashboardPage'], 99);
        $this->app->addAction('admin_init', [$this, 'redirectToWelcomePage']);
        $this->app->addAction('admin_notices', [$this, 'customerEmailSubscribe']);
        $this->app->addAction('wp_ajax_smartpay_contact_optin_notice_dismiss', [$this, 'customerOptinNoticeDismiss']);
        $this->app->addFilter('admin_footer_text', [$this, 'adminFooterText']);
        $this->app->addFilter('update_footer', [$this, 'adminFooterVersion'], 11);
    }

    public function adminMenu()
    {
        add_menu_page(
            __('SmartPay', 'smartpay'),
            __('SmartPay', 'smartpay'),
            'manage_options',
            'smartpay',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            },
            smartpay_svg_icon(),
            30
        );

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

        add_submenu_page(
            'smartpay',
            __('SmartPay - Products', 'smartpay'),
            __('Products', 'smartpay'),
            'manage_options',
            'smartpay#/products',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('admin');
            }
        );

        add_submenu_page(
            'smartpay',
            __('SmartPay - Forms', 'smartpay'),
            __('Forms (Legacy)', 'smartpay'),
            'manage_options',
            'smartpay-form',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('form-builder');
            }
        );

        $this->smartpayProMenu();

        do_action('smartpay_admin_add_menu_items');

        add_submenu_page(
            'smartpay',
            __('SmartPay - Customers', 'smartpay'),
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
            __('SmartPay - Coupons', 'smartpay'),
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
            __('SmartPay - Payments', 'smartpay'),
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
            __('SmartPay - Settings', 'smartpay'),
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
            __('SmartPay - Integrations', 'smartpay'),
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
            __('Getting Started', 'smartpay'),
            __('Getting Started', 'smartpay'),
            'manage_options',
            'wpsmartpay-getting-started',
            [$this, 'outputDashboardMarkup']
        );

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
                'title' => $icon . esc_html__( 'SmartPay', 'smartpay' ),
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
                'id'     => 'smartpay-toolbar-payments',
                'title'  => esc_html__( 'Payments', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay#/payments' ) ),
            )
        );

        $wp_admin_bar->add_node(
            array(
                'parent' => 'smartpay-toolbar',
                'id'     => 'smartpay-toolbar-customers',
                'title'  => esc_html__( 'Customers', 'smartpay' ),
                'href'   => esc_url( admin_url( 'admin.php?page=smartpay#/customers' ) ),
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
        $request_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $is_main_admin_page = in_array($request_page, ['smartpay', 'smartpay#/products', 'smartpay#/customers', 'smartpay#/coupons', 'smartpay#/payments'], true);

        $admin_style_hooks = [
            'toplevel_page_smartpay',
            'smartpay_page_smartpay-form',
            'smartpay_page_smartpay-setting',
            'smartpay_page_smartpay-integrations',
            'smartpay_page_smartpay#/products',
            'smartpay_page_smartpay#/customers',
            'smartpay_page_smartpay#/coupons',
            'smartpay_page_smartpay#/payments',
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
                    'options' => $this->getOptionsScriptsData(),
					'logo' => SMARTPAY_PLUGIN_ASSETS . '/img/logo.png',
					'version' => SMARTPAY_VERSION,
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

        if ('smartpay_page_wpsmartpay-getting-started' === $hook) {
            wp_register_style('smartpay-getting-started', false, array(), SMARTPAY_VERSION); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
            wp_enqueue_style('smartpay-getting-started');
            wp_add_inline_style(
                'smartpay-getting-started',
                '#wpcontent { padding-left:0; } .sp-gs { margin-top:-10px; } ' . $this->gettingStartedInlineCSS()
            );
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
        return [
            'currency'          => smartpay_get_currency(),
            'currencySymbol'    => smartpay_get_currency_symbol(),
            'isTestMode'        => smartpay_is_test_mode(),
        ];
    }

    public function smartpayDebugLogClear()
    {
        $smartpayLogs = new Logger();
        $smartpayLogs->clear_log_file();

        $smartpay_settings = get_option('smartpay_settings', []);
        $smartpay_settings['smartpay_debug_log'] = null;
        update_option('smartpay_settings', $smartpay_settings);
        die();
    }


    public function outputDashboardMarkup()
    {
        $user              = wp_get_current_user();
        $first_name        = ! empty( $user->first_name ) ? $user->first_name : $user->display_name;
        $gateway_active    = ! empty( smartpay_get_default_gateway() );
        $has_products      = Product::count() > 0;
        $has_forms         = Form::count() > 0;
        $has_payments      = Payment::where( 'status', Payment::COMPLETED )->count() > 0;

        $steps = array(
            array(
                'done'  => true,
                'title' => __( 'Install SmartPay', 'smartpay' ),
                'desc'  => __( 'Plugin is active and running.', 'smartpay' ),
                'url'   => null,
                'cta'   => null,
            ),
            array(
                'done'  => $gateway_active,
                'title' => __( 'Connect a payment gateway', 'smartpay' ),
                'desc'  => __( 'Accept real payments via Stripe, PayPal, Paddle, or another gateway.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay-setting&tab=gateways' ),
                'cta'   => __( 'Configure gateway', 'smartpay' ),
            ),
            array(
                'done'  => $has_products,
                'title' => __( 'Create your first product', 'smartpay' ),
                'desc'  => __( 'Define what you\'re selling — digital file, service, or subscription.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/products/create' ),
                'cta'   => __( 'Add a product', 'smartpay' ),
            ),
            array(
                'done'  => $has_forms,
                'title' => __( 'Create a payment form', 'smartpay' ),
                'desc'  => __( 'Build a checkout form and embed it on any page with a shortcode.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/native-forms' ),
                'cta'   => __( 'Create a form', 'smartpay' ),
            ),
            array(
                'done'  => $has_payments,
                'title' => __( 'Receive your first payment', 'smartpay' ),
                'desc'  => __( 'Share your form link and start collecting payments.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/payments' ),
                'cta'   => __( 'View payments', 'smartpay' ),
            ),
        );

        $done_count  = count( array_filter( $steps, fn( $s ) => $s['done'] ) );
        $total       = count( $steps );
        $progress    = (int) round( ( $done_count / $total ) * 100 );
        $active_step = null;
        foreach ( $steps as $i => $step ) {
            if ( ! $step['done'] ) {
                $active_step = $i;
                break;
            }
        }

        $features = array(
            array(
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2ZM16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2Z"/></svg>',
                'label' => __( 'Products', 'smartpay' ),
                'desc'  => __( 'Manage digital products and pricing.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/products' ),
            ),
            array(
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/></svg>',
                'label' => __( 'Forms', 'smartpay' ),
                'desc'  => __( 'Build and embed payment forms anywhere.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/native-forms' ),
            ),
            array(
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3Z"/></svg>',
                'label' => __( 'Payments', 'smartpay' ),
                'desc'  => __( 'View and manage all transactions.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/payments' ),
            ),
            array(
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15"/></svg>',
                'label' => __( 'Subscriptions', 'smartpay' ),
                'desc'  => __( 'Track recurring billing and subscribers.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/subscriptions' ),
            ),
            array(
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2Zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 0-2 2h-2a2 2 0 0 0-2-2Z"/></svg>',
                'label' => __( 'Reports', 'smartpay' ),
                'desc'  => __( 'Revenue insights, forms, and goal metrics.', 'smartpay' ),
                'url'   => admin_url( 'admin.php?page=smartpay#/reports' ),
            ),
        );

        $all_done = ( $done_count === $total );
        ?>
        <div class="sp-gs">

            <?php /* ── Header ── */ ?>
            <div class="sp-gs-header">
                <div class="sp-gs-header__inner">
                    <div class="sp-gs-header__left">
                        <img class="sp-gs-logo" src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo.png' ); ?>" alt="<?php esc_attr_e( 'SmartPay', 'smartpay' ); ?>">
                        <div>
                            <h1 class="sp-gs-header__title">
                                <?php
                                printf(
                                    /* translators: %s: user first name */
                                    esc_html__( 'Welcome, %s!', 'smartpay' ),
                                    esc_html( $first_name )
                                );
                                ?>
                            </h1>
                            <p class="sp-gs-header__sub">
                                <?php
                                if ( $all_done ) {
                                    esc_html_e( "You're all set — SmartPay is ready to collect payments.", 'smartpay' );
                                } else {
                                    printf(
                                        /* translators: 1: completed steps 2: total steps */
                                        esc_html__( 'Complete the setup checklist to start accepting payments. %1$d of %2$d steps done.', 'smartpay' ),
                                        (int) $done_count,
                                        (int) $total
                                    );
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="sp-gs-header__progress">
                        <div class="sp-gs-progress-bar">
                            <div class="sp-gs-progress-bar__fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
                        </div>
                        <span class="sp-gs-progress-label">
                            <?php
                            printf(
                                /* translators: 1: completed steps 2: total steps */
                                esc_html__( '%1$d / %2$d', 'smartpay' ),
                                (int) $done_count,
                                (int) $total
                            );
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php /* ── Body ── */ ?>
            <div class="sp-gs-body">
                <div class="sp-gs-main">

                    <?php /* ── Checklist ── */ ?>
                    <div class="sp-gs-card">
                        <h2 class="sp-gs-card__title"><?php esc_html_e( 'Setup Checklist', 'smartpay' ); ?></h2>
                        <p class="sp-gs-card__desc"><?php esc_html_e( 'Follow these steps to get SmartPay ready for your first payment.', 'smartpay' ); ?></p>
                        <div class="sp-gs-steps">
                            <?php foreach ( $steps as $i => $step ) : ?>
                                <?php
                                $is_active  = ( $i === $active_step );
                                $step_class = 'sp-gs-step';
                                if ( $step['done'] ) {
                                    $step_class .= ' sp-gs-step--done';
                                } elseif ( $is_active ) {
                                    $step_class .= ' sp-gs-step--active';
                                }
                                ?>
                                <div class="<?php echo esc_attr( $step_class ); ?>">
                                    <div class="sp-gs-step__icon">
                                        <?php if ( $step['done'] ) : ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        <?php elseif ( $is_active ) : ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                                        <?php else : ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sp-gs-step__content">
                                        <div class="sp-gs-step__title"><?php echo esc_html( $step['title'] ); ?></div>
                                        <div class="sp-gs-step__desc"><?php echo esc_html( $step['desc'] ); ?></div>
                                    </div>
                                    <?php if ( $step['url'] && $step['cta'] ) : ?>
                                        <a href="<?php echo esc_url( $step['url'] ); ?>" class="sp-gs-step__btn <?php echo $step['done'] ? 'sp-gs-step__btn--done' : ''; ?>">
                                            <?php echo esc_html( $step['cta'] ); ?>
                                            <?php if ( ! $step['done'] ) : ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php /* ── Feature cards ── */ ?>
                    <div class="sp-gs-card sp-gs-card--plain">
                        <h2 class="sp-gs-card__title"><?php esc_html_e( 'Explore SmartPay', 'smartpay' ); ?></h2>
                        <p class="sp-gs-card__desc"><?php esc_html_e( 'Jump into any section of your dashboard.', 'smartpay' ); ?></p>
                        <div class="sp-gs-features">
                            <?php foreach ( $features as $feature ) : ?>
                                <a href="<?php echo esc_url( $feature['url'] ); ?>" class="sp-gs-feature">
                                    <div class="sp-gs-feature__icon"><?php echo $feature['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded, not user input ?></div>
                                    <div class="sp-gs-feature__label"><?php echo esc_html( $feature['label'] ); ?></div>
                                    <div class="sp-gs-feature__desc"><?php echo esc_html( $feature['desc'] ); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <?php /* ── Sidebar ── */ ?>
                <aside class="sp-gs-sidebar">
                    <div class="sp-gs-card">
                        <h3 class="sp-gs-card__title"><?php esc_html_e( 'Resources', 'smartpay' ); ?></h3>
                        <ul class="sp-gs-links">
                            <li>
                                <a href="https://docs.wpsmartpay.com/" target="_blank" rel="noopener noreferrer" class="sp-gs-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <?php esc_html_e( 'Documentation', 'smartpay' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.youtube.com/embed/PdqA7XNH60Q" target="_blank" rel="noopener noreferrer" class="sp-gs-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                    <?php esc_html_e( 'Video Overview', 'smartpay' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://wpsmartpay.com/support/" target="_blank" rel="noopener noreferrer" class="sp-gs-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                                    <?php esc_html_e( 'Contact Support', 'smartpay' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://wpsmartpay.com/changelog/" target="_blank" rel="noopener noreferrer" class="sp-gs-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75"/></svg>
                                    <?php esc_html_e( 'Changelog', 'smartpay' ); ?>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="sp-gs-card sp-gs-version-card">
                        <div class="sp-gs-version-badge">Pro</div>
                        <div class="sp-gs-version-text">
                            <span class="sp-gs-version-name">SmartPay</span>
                            <span class="sp-gs-version-num">v<?php echo esc_html( SMARTPAY_VERSION ); ?></span>
                        </div>
                        <a href="https://wpsmartpay.com/changelog/" target="_blank" rel="noopener noreferrer" class="sp-gs-version-link"><?php esc_html_e( "What's new", 'smartpay' ); ?> &rarr;</a>
                    </div>
                </aside>
            </div>
        </div>
        <?php
    }

    private function gettingStartedInlineCSS(): string
    {
        return '
        .sp-gs { background:#f9fafb; min-height:calc(100vh - 32px); padding-bottom:48px; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; }

        /* Header */
        .sp-gs-header { background:#fff; border-bottom:1px solid #e5e7eb; padding:0; }
        .sp-gs-header__inner { display:flex; align-items:center; justify-content:space-between; gap:24px; max-width:1100px; margin:0 auto; padding:22px 32px; flex-wrap:wrap; }
        .sp-gs-header__left { display:flex; align-items:center; gap:16px; }
        .sp-gs-logo { height:36px; width:auto; display:block; }
        .sp-gs-header__title { color:#111827; font-size:20px; font-weight:700; margin:0 0 3px; line-height:1.2; }
        .sp-gs-header__sub { color:#6b7280; font-size:13px; margin:0; }
        .sp-gs-header__progress { display:flex; align-items:center; gap:10px; }
        .sp-gs-progress-bar { width:140px; height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden; }
        .sp-gs-progress-bar__fill { height:100%; background:#6366f1; border-radius:999px; transition:width .4s ease; }
        .sp-gs-progress-label { font-size:12px; font-weight:600; color:#6b7280; white-space:nowrap; }

        /* Body layout */
        .sp-gs-body { display:grid; grid-template-columns:1fr 280px; gap:24px; max-width:1100px; margin:28px auto 0; padding:0 32px; }
        .sp-gs-main { display:flex; flex-direction:column; gap:20px; min-width:0; }

        /* Cards */
        .sp-gs-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px 28px; box-shadow:0 1px 3px rgba(0,0,0,.05); }
        .sp-gs-card--plain { background:#fff; }
        .sp-gs-card__title { font-size:15px; font-weight:700; color:#111827; margin:0 0 4px; }
        .sp-gs-card__desc { font-size:13px; color:#6b7280; margin:0 0 20px; }

        /* Checklist steps */
        .sp-gs-steps { display:flex; flex-direction:column; gap:0; }
        .sp-gs-step { display:flex; align-items:center; gap:14px; padding:14px 0; border-bottom:1px solid #f3f4f6; position:relative; }
        .sp-gs-step:last-child { border-bottom:none; padding-bottom:0; }
        .sp-gs-step:first-child { padding-top:0; }
        .sp-gs-step--active { background:#fafafa; margin:0 -28px; padding:16px 28px; border-radius:8px; border-bottom:none; border-left:3px solid #6366f1; }
        .sp-gs-step--active + .sp-gs-step { border-top:1px solid #f3f4f6; }
        .sp-gs-step__icon { flex-shrink:0; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
        .sp-gs-step--done .sp-gs-step__icon { background:#dcfce7; color:#16a34a; }
        .sp-gs-step--active .sp-gs-step__icon { background:#eef2ff; color:#6366f1; }
        .sp-gs-step:not(.sp-gs-step--done):not(.sp-gs-step--active) .sp-gs-step__icon { background:#f3f4f6; color:#9ca3af; }
        .sp-gs-step__content { flex:1; min-width:0; }
        .sp-gs-step__title { font-size:13.5px; font-weight:600; color:#111827; margin-bottom:2px; }
        .sp-gs-step--done .sp-gs-step__title { color:#6b7280; }
        .sp-gs-step__desc { font-size:12px; color:#9ca3af; line-height:1.5; }
        .sp-gs-step--active .sp-gs-step__desc { color:#6b7280; }
        .sp-gs-step__btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:7px; font-size:12.5px; font-weight:500; text-decoration:none; white-space:nowrap; flex-shrink:0; transition:background .15s,border-color .15s; border:1px solid #6366f1; background:#6366f1; color:#fff; }
        .sp-gs-step__btn:hover { background:#4f46e5; border-color:#4f46e5; color:#fff; text-decoration:none; }
        .sp-gs-step__btn--done { background:#fff; border-color:#e5e7eb; color:#6b7280; font-size:12px; }
        .sp-gs-step__btn--done:hover { background:#f9fafb; color:#374151; text-decoration:none; }

        /* Feature cards */
        .sp-gs-features { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px; }
        .sp-gs-feature { display:flex; flex-direction:column; gap:8px; padding:16px; border:1px solid #e5e7eb; border-radius:10px; text-decoration:none; background:#fff; transition:border-color .15s,box-shadow .15s; }
        .sp-gs-feature:hover { border-color:#c7d2fe; box-shadow:0 2px 8px rgba(99,102,241,.08); text-decoration:none; }
        .sp-gs-feature__icon { color:#6366f1; }
        .sp-gs-feature__label { font-size:13.5px; font-weight:600; color:#111827; }
        .sp-gs-feature__desc { font-size:12px; color:#6b7280; line-height:1.4; }

        /* Sidebar */
        .sp-gs-sidebar { display:flex; flex-direction:column; gap:16px; }
        .sp-gs-links { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:2px; }
        .sp-gs-link { display:flex; align-items:center; gap:10px; padding:9px 10px; border-radius:7px; font-size:13px; color:#374151; text-decoration:none; transition:background .15s; }
        .sp-gs-link:hover { background:#f3f4f6; color:#111827; text-decoration:none; }
        .sp-gs-link svg { flex-shrink:0; color:#6b7280; }

        /* Version card */
        .sp-gs-version-card { display:flex; align-items:center; gap:10px; padding:16px 20px; }
        .sp-gs-version-badge { font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; background:#eef2ff; color:#6366f1; border-radius:5px; padding:3px 8px; }
        .sp-gs-version-text { display:flex; flex-direction:column; flex:1; }
        .sp-gs-version-name { font-size:13px; font-weight:600; color:#111827; }
        .sp-gs-version-num { font-size:12px; color:#6b7280; }
        .sp-gs-version-link { font-size:12px; color:#6366f1; text-decoration:none; white-space:nowrap; }
        .sp-gs-version-link:hover { text-decoration:underline; }

        /* Responsive */
        @media (max-width:900px) {
            .sp-gs-body { grid-template-columns:1fr; padding:0 16px; }
            .sp-gs-header__inner { padding:18px 16px; }
            .sp-gs-progress-bar { width:100px; }
        }
        @media (max-width:600px) {
            .sp-gs-header__inner { flex-direction:column; align-items:flex-start; }
            .sp-gs-features { grid-template-columns:1fr 1fr; }
        }
        ';
    }

    public function redirectToWelcomePage()
    {
        if (!get_transient('wpsmartpay_activation_redirect')) {
            return;
        }

        delete_transient('wpsmartpay_activation_redirect');

	    // phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce needed
        if (isset($_GET['activate-multi']) || is_network_admin()) {
            return;
        }

        wp_safe_redirect(admin_url('admin.php?page=' . 'wpsmartpay-getting-started'));
        exit;
    }

    public function customerEmailSubscribe()
    {
        $admin_page = get_current_screen();
        $user = wp_get_current_user();
        $user_id = $user->ID;

        // if customer opted in notice will never show
        if (get_user_meta($user_id, 'smartpay_opted_in_dismissed_at', true)) {
            return;
        }

        $second_time_dismissed_at = get_user_meta($user_id, 'smartpay_optin_second_time_dismissed_at', true);
        $first_time_dismissed_at = get_user_meta($user_id, 'smartpay_optin_first_time_dismissed_at', true);

        if (
            get_user_meta($user_id, 'smartpay_optin_third_time_dismissed_at', true)
        ) {
            return; // was dismissed 3 times
        } elseif ($second_time_dismissed_at) {
            $month_ago = time() - MONTH_IN_SECONDS;
            if ($month_ago < $second_time_dismissed_at) {
                return; // hide if dismissed less then month ago
            }
            $dismiss_key = 'optin_third_time';
        } elseif ($first_time_dismissed_at) {
            $week_ago = time() - WEEK_IN_SECONDS;
            if ($week_ago < $first_time_dismissed_at) {
                return; // hide if dismissed less then week ago
            }
            $dismiss_key = 'optin_second_time';
        } else {
            // was never dismissed
            $dismiss_key = 'optin_first_time';
        }

        if ('smartpay_page_wpsmartpay-getting-started' !==  $admin_page->base) :
        ?>
            <div class="notice notice-warning is-dismissible smartpay-notice-wrapper">
                <img src="<?php echo esc_url(SMARTPAY_PLUGIN_ASSETS . '/img/favicon.png'); ?>" alt="<?php esc_attr_e('Logo', 'smartpay') ?>">
                <div class="smartpay-notice-content">
                    <h4><?php esc_html_e('Wanna get some discount for WP SmartPay Pro? No Worries!! We got you!! give us your email we will send you the discount code.', 'smartpay') ?></h4>
                    <form style="display:flex">
                        <div class="smartpay-notice-input-wrapper">
                            <input type="text" value="<?php echo esc_attr($user->first_name); ?>" placeholder="<?php esc_attr_e('Name', 'smartpay'); ?>" />
                        </div>
                        <div class="smartpay-notice-input-wrapper">
                            <input type="email" value="<?php echo esc_attr($user->user_email); ?>" required placeholder="<?php esc_attr_e('Email Address', 'smartpay'); ?>" />
                        </div>
                        <button type="submit" class="button button-primary subscribe-button"><?php esc_html_e('Get Discount', 'smartpay'); ?></button>
                    </form>
                </div>
            </div>

            <style type="text/css">
                .smartpay-notice-wrapper {
                    padding: 15px;
                    display: flex;
                    align-items: center;
                }

                .smartpay-notice-wrapper img {
                    width: 50px;
                    max-width: 50px;
                    height: 50px;
                    margin-right: 15px;
                }

                .smartpay-notice-wrapper h4 {
                    margin-top: 0;
                }

                .smartpay-notice-input-wrapper {
                    margin-right: 15px;
                }
            </style>

            <script type="text/javascript">
                jQuery(document).on('click', '.smartpay-notice-wrapper .notice-dismiss', function(event) {
                    event.preventDefault();
                    jQuery.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'smartpay_contact_optin_notice_dismiss',
                            nonce: '<?php echo esc_attr(wp_create_nonce('smartpay_contact_optin_notice_dismiss')); ?>',
                            user_id: '<?php echo esc_attr($user_id); ?>',
                            meta_value: '<?php echo esc_attr($dismiss_key); ?>'
                        }
                    })
                });

                jQuery(document).on('submit', '.smartpay-notice-wrapper .smartpay-notice-content form', function(event) {
                    event.preventDefault();
                    let form = jQuery(this);
                    let name = form.find('input[type=text]').val();
                    let email = form.find('input[type=email]').val();
                    jQuery.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        crossDomain: true,
                        data: {
                            action: 'smartpay_customer_contact_optin',
                            name: name,
                            email: email,
                        },
                        success(response) {
                            form.parents('.smartpay-notice-wrapper').fadeOut();
                            if (response.success) {
                                jQuery.ajax({
                                    url: ajaxurl,
                                    method: 'POST',
                                    data: {
                                        action: 'smartpay_contact_optin_notice_dismiss',
                                        nonce: '<?php echo esc_attr(wp_create_nonce('smartpay_contact_optin_notice_dismiss')); ?>',
                                        user_id: '<?php echo esc_attr($user_id); ?>',
                                        meta_value: 'opted_in'
                                    }
                                })
                            }
                        }
                    })
                });
            </script>

<?php
        endif;
    }

    public function customerOptinNoticeDismiss()
    {
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])): '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'smartpay_contact_optin_notice_dismiss')) {
            return;
        }

        if (empty($_REQUEST['user_id']) || empty($_REQUEST['meta_value'])) {
            return;
        }

        $meta_value = 'smartpay_' . sanitize_text_field(wp_unslash($_REQUEST['meta_value'])) . '_dismissed_at';
        $userId = sanitize_text_field(wp_unslash($_REQUEST['user_id']));
        update_user_meta($userId, $meta_value, time());
        wp_die();
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
            /* translators: %s: five-star rating link */
            wp_kses(
                __( 'If you like <strong>WP SmartPay</strong> please leave us a %s rating. A huge thanks in advance!', 'smartpay' ),
                [ 'strong' => [] ]
            ),
            '<a href="' . esc_url( $rate_url ) . '" target="_blank" rel="noopener noreferrer" style="color:#f0ad4e;text-decoration:none;" aria-label="' . esc_attr__( 'Rate WP SmartPay on WordPress.org', 'smartpay' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
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
