<?php

namespace SmartPay\Modules\Admin;

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
            $user = wp_get_current_user();
            $user_id = $user->ID;
            wp_enqueue_style('smartpay-dashboard', SMARTPAY_PLUGIN_ASSETS . '/css/dashboard.css', '', SMARTPAY_VERSION);
            wp_enqueue_script('smartpay-dashboard', SMARTPAY_PLUGIN_ASSETS . '/js/dashboard.js', ['jquery'], SMARTPAY_VERSION, true);

            wp_localize_script('smartpay-dashboard', 'dashboardObj', [
	            'wp_json_url' => site_url('wp-json'),
                'user_id'   => $user_id,
                'nonce'     => wp_create_nonce('smartpay_contact_optin_notice_dismiss')
            ]);
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
        $user = wp_get_current_user();
?>
        <div class="wpsmartpay-welcome">
            <div class="container">
                <div class="introduction-image">
                    <img src="<?php echo esc_url(SMARTPAY_PLUGIN_ASSETS . '/img/logo.png') ?>" alt="<?php esc_attr_e('SmartPay Logo', 'smartpay'); ?>">
                </div>
                <div class="introduction">
                    <div class="introduction-text">
                        <h2><?php esc_html_e('Welcome Aboard', 'smartpay'); ?></h2>
                        <p><?php esc_html_e('Congratulations you are just few minutes away form displaying your digital products to selling it and receiving payments, all-in-one Simplest solution.', 'smartpay'); ?></p>
                    </div>
                    <div class="introduction-video">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/PdqA7XNH60Q" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

                        <!--                        <p>--><?php //_e('Spend 3 minutes ( literally 3 minutes ) watching the video to get an overview how it works.');
                                                            ?>
                        <!--</p>-->
                    </div>
                </div>

                <div class="subscription-form">
                    <h3><?php esc_html_e('Wanna get some discount?', 'smartpay'); ?></h3>
                    <p><?php esc_html_e('No Worries!! We got you!! give us your email we will send you the discount code.', 'smartpay'); ?></p>
                    <form>
                        <div class="inline-input-wrapper">
                            <input type="email" placeholder="<?php esc_attr_e('Email Address', 'smartpay'); ?>" value="<?php echo esc_attr($user->user_email); ?>" />
                            <button type="submit" class="button button-primary"><?php esc_html_e('Get Discount', 'smartpay'); ?></button>
                        </div>
                    </form>
                </div>

                <div class="create-form-section">
                    <div class="button-wrap">
                        <div class="left-side">
                            <a class="dashboard-button button" href="<?php echo esc_url(admin_url('admin.php?page="smartpay-form#/create"')); ?>"><?php esc_html_e('Create Your First Form', 'smartpay'); ?></a>
                        </div>
                        <div class="right-side">
                            <a class="dashboard-button button" href="<?php echo esc_url(admin_url('admin.php?page="smartpay#/products/create"')); ?>"><?php esc_html_e('Create Your First Product', 'smartpay'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
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
