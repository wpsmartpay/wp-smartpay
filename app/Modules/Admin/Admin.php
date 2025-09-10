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
        $this->app->addAction('wp_ajax_smartpay_debug_log_clear', [$this, 'smartpayDebugLogClear']);
        $this->app->addAction('smartpay_admin_add_menu_items', [$this, 'registerDashboardPage'], 99);
        $this->app->addAction('admin_init', [$this, 'redirectToWelcomePage']);
        $this->app->addAction('admin_notices', [$this, 'customerEmailSubscribe']);
        $this->app->addAction('wp_ajax_smartpay_contact_optin_notice_dismiss', [$this, 'customerOptinNoticeDismiss']);
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
            __('Forms', 'smartpay'),
            'manage_options',
            'smartpay-form',
            function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
                echo smartpay_view('form-builder');
            }
        );

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

        $this->smartpayProMenu();

        do_action('smartpay_admin_add_menu_items');
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
        if ('toplevel_page_smartpay' === $hook || 'smartpay_page_smartpay-form' === $hook || 'smartpay_page_smartpay-setting' === $hook || 'smartpay_page_smartpay-integrations' === $hook) {
            wp_register_style('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/css/admin.css', '', SMARTPAY_VERSION);
            wp_enqueue_style('smartpay-admin');
        }
        if ('toplevel_page_smartpay' === $hook) {
            wp_register_script('smartpay-admin', SMARTPAY_PLUGIN_ASSETS . '/js/admin.js', ['jquery', 'wp-element', 'wp-data'], SMARTPAY_VERSION, true);
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

    public function registerDashboardPage()
    {
        add_submenu_page(
            'smartpay',
            __('Getting Started', 'smartpay'),
            __('Getting Started', 'smartpay'),
            'manage_options',
            'wpsmartpay-getting-started',
            [$this, 'outputDashboardMarkup']
        );
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
                        url: 'https://localhost/boss/wp-admin/admin-ajax.php',
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
        if (empty($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'smartpay_contact_optin_notice_dismiss')) {
            return;
        }

        if (empty($_REQUEST['user_id']) || empty($_REQUEST['meta_value'])) {
            return;
        }

        $meta_value = 'smartpay_' . sanitize_text_field($_REQUEST['meta_value']) . '_dismissed_at';
        $userId = sanitize_text_field($_REQUEST['user_id']);
        update_user_meta($userId, $meta_value, time());
        wp_die();
    }
}
