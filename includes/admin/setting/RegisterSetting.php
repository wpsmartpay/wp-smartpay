<?php

namespace ThemesGrove\SmartPay\Admin\Setting;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class RegisterSetting
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct RegisterSetting class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Main RegisterSetting Instance.
     *
     * Ensures that only one instance of RegisterSetting exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|RegisterSetting
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof RegisterSetting)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add all settings sections and fields
     *
     * @since 1.0
     * @return void
     */
    public function register_settings()
    {
        if (false == get_option('smartpay_settings')) {
            add_option('smartpay_settings');
        }

        foreach ($this->registered_settings() as $tab => $sections) {
            foreach ($sections as $section => $settings) {
                // Check for backwards compatibility
                $section_tabs = $this->settings_tab_sections($tab);
                if (!is_array($section_tabs) || !array_key_exists($section, $section_tabs)) {
                    $section = 'main';
                    $settings = $sections;
                }

                add_settings_section(
                    'smartpay_settings_' . $tab . '_' . $section,
                    __return_null(),
                    '__return_false',
                    'smartpay_settings_' . $tab . '_' . $section
                );

                foreach ($settings as $option) {

                    // For backwards compatibility
                    if (empty($option['id'])) {
                        continue;
                    }

                    $args = wp_parse_args($option, array(
                        'section'       => $section,
                        'id'            => null,
                        'desc'          => '',
                        'name'          => '',
                        'size'          => null,
                        'options'       => '',
                        'std'           => '',
                        'min'           => null,
                        'max'           => null,
                        'step'          => null,
                        'chosen'        => null,
                        'multiple'      => null,
                        'placeholder'   => null,
                        'allow_blank'   => true,
                        'readonly'      => false,
                        'faux'          => false,
                        'tooltip_title' => false,
                        'tooltip_desc'  => false,
                        'field_class'   => '',
                    ));

                    add_settings_field(
                        'smartpay_settings[' . $args['id'] . ']',
                        $args['name'],
                        method_exists($this, 'settings_'. $args['type'] . '_callback') ? [$this, 'settings_' . $args['type'] . '_callback'] : [$this, 'settings_missing_callback'],
                        'smartpay_settings_' . $tab . '_' . $section,
                        'smartpay_settings_' . $tab . '_' . $section,
                        $args
                    );
                }
            }
        }

        // Creates our settings in the options table
        register_setting('smartpay_settings', 'smartpay_settings', [$this, 'settings_sanitize']);
    }

    /**
     * Retrieve the array of plugin settings
     *
     * @since 1.8
     * @return array
     */
    public static function registered_settings()
    {
        $smartpay_settings = array(
            /** General Settings */
            'general' => apply_filters(
                'smartpay_settings_general',
                array(
                    'main' => array(
                        'page_settings' => array(
                            'id'   => 'page_settings',
                            'name' => '<h3>' . __('Pages', 'wp-smartpay') . '</h3>',
                            'desc' => '',
                            'type' => 'header',
                            'tooltip_title' => __('Page Settings', 'wp-smartpay'),
                            'tooltip_desc'  => __('Easy Digital Downloads uses the pages below for handling the display of checkout, purchase confirmation, purchase history, and purchase failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'wp-smartpay'),
                        ),
                        'allow_tracking' => array(
                            'id'   => 'allow_tracking',
                            'name' => __('Allow Usage Tracking?', 'wp-smartpay'),
                            'desc' => sprintf(
                                __('Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a discount to the smartpay shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'wp-smartpay'),
                                'https://easydigitaldownloads.com/downloads/?utm_source=' . substr(md5(get_bloginfo('name')), 0, 10) . '&utm_medium=admin&utm_term=settings&utm_campaign=smartpayUsageTracking'
                            ),
                            'type' => 'checkbox',
                        ),
                    ),
                )
            ),
            /** Payment Gateways Settings */
            'gateways' => apply_filters(
                'smartpay_settings_gateways',
                array(
                    'main' => array(
                        'test_mode' => array(
                            'id'   => 'test_mode',
                            'name' => __('Test Mode', 'wp-smartpay'),
                            'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'wp-smartpay'),
                            'type' => 'checkbox',
                        ),
                        'accepted_cards' => array(
                            'id'      => 'accepted_cards',
                            'name'    => __('Accepted Payment Method Icons', 'wp-smartpay'),
                            'desc'    => __('Display icons for the selected payment methods.', 'wp-smartpay') . '<br/>' . __('You will also need to configure your gateway settings if you are accepting credit cards.', 'wp-smartpay'),
                            'type'    => 'payment_icons',
                            'options' => apply_filters(
                                'smartpay_accepted_payment_icons',
                                array(
                                    'mastercard'      => 'Mastercard',
                                    'visa'            => 'Visa',
                                    'americanexpress' => 'American Express',
                                    'discover'        => 'Discover',
                                    'paypal'          => 'PayPal',
                                )
                            ),
                        ),
                    ),
                )
            ),
            /** Emails Settings */
            'emails' => apply_filters(
                'smartpay_settings_emails',
                array(
                    'main' => array(
                        'sendwp_header' => array(
                            'id'   => 'sendwp_header',
                            'name' => '<strong>' . __('SendWP Settings', 'wp-smartpay') . '</strong>',
                            'type' => 'header',
                        ),
                        'email_header' => array(
                            'id'   => 'email_header',
                            'name' => '<strong>' . __('Email Configuration', 'wp-smartpay') . '</strong>',
                            'type' => 'header',
                        ),
                    ),
                )
            ),
            /** License Settings */
            'licenses' => apply_filters(
                'smartpay_settings_licenses',
                array(
                    'main' => array(
                        'hea' => array(
                            'id'   => 'hea',
                            'name' => '<strong>' . __('SendWP Settings', 'wp-smartpay') . '</strong>',
                            'type' => 'header',
                        ),
                    ),
                )
            ),
        );

        return apply_filters('smartpay_settings', $smartpay_settings);
    }

    /**
     * Retrieve settings tabs
     *
     * @since 2.5
     * @return array $section
     */
    public static function settings_tab_sections($tab = false)
    {
        $tabs     = array();
        $sections = self::get_registered_settings_sections();

        if ($tab && !empty($sections[$tab])) {
            $tabs = $sections[$tab];
        } elseif ($tab) {
            $tabs = array();
        }

        return $tabs;
    }

    /**
     * Get the settings sections for each tab
     * Uses a static to avoid running the filters on every request to this function
     *
     * @since  2.5
     * @return array Array of tabs and sections
     */
    public static function get_registered_settings_sections()
    {
        static $sections = false;

        if (false !== $sections) {
            return $sections;
        }

        $sections = array(
            'general'   => apply_filters('smartpay_settings_sections_general', array(
                'main'  => __('General', 'wp-smartpay'),
            )),
            'gateways'  => apply_filters('smartpay_settings_sections_gateways', array(
                'main'  => __('General', 'wp-smartpay'),
            )),
            'emails'    => apply_filters('smartpay_settings_sections_emails', array(
                'main'  => __('General', 'wp-smartpay'),
            )),
            'licenses'  => apply_filters('smartpay_settings_sections_licenses', array(
                'main'  => __('General', 'wp-smartpay'),
            )),
        );

        return apply_filters('smartpay_settings_sections', $sections);
    }

    public static function settings_tabs()
    {
        $tabs = array();
        $tabs['general']  = __('General', 'wp-smartpay');
        $tabs['gateways'] = __('Payment Gateways', 'wp-smartpay');
        $tabs['emails']   = __('Emails', 'wp-smartpay');
        $tabs['licenses']   = __('Licenses', 'wp-smartpay');

        return apply_filters('smartpay_settings_tabs', $tabs);
    }

    public function settings_sanitize($input = array())
    {
        global $smartpay_options;

        $doing_section = false;
        if (!empty($_POST['_wp_http_referer'])) {
            $doing_section = true;
        }

        $setting_types = $this->_registered_settings_types();
        $input         = $input ? $input : array();

        if ($doing_section) {
            parse_str($_POST['_wp_http_referer'], $referrer); // Pull out the tab and section
            $tab      = isset($referrer['tab']) ? $referrer['tab'] : 'general';
            $section  = isset($referrer['section']) ? $referrer['section'] : 'main';

            if (!empty($_POST['smartpay_section_override'])) {
                $section = sanitize_text_field($_POST['smartpay_section_override']);
            }

            $setting_types = $this->_registered_settings_types($tab, $section);

            // Run a general sanitization for the tab for special fields (like taxes)
            $input = apply_filters('smartpay_settings_' . $tab . '_sanitize', $input);

            // Run a general sanitization for the section so custom tabs with sub-sections can save special data
            $input = apply_filters('smartpay_settings_' . $tab . '-' . $section . '_sanitize', $input);
        }

        // Merge our new settings with the existing
        $output = array_merge($smartpay_options, $input);

        foreach ($setting_types as $key => $type) {
            if (empty($type)) {
                continue;
            }

            // Some setting types are not actually settings, just keep moving along here
            $non_setting_types = apply_filters('smartpay_non_setting_types', array(
            'header', 'descriptive_text', 'hook',
        ));

            if (in_array($type, $non_setting_types)) {
                continue;
            }

            if (array_key_exists($key, $output)) {
                $output[$key] = apply_filters('settings_sanitize_' . $type, $output[$key], $key);
                $output[$key] = apply_filters('settings_sanitize', $output[$key], $key);
            }

            if ($doing_section) {
                switch ($type) {
                case 'checkbox':
                case 'gateways':
                case 'multicheck':
                case 'payment_icons':
                    if (array_key_exists($key, $input) && $output[$key] === '-1') {
                        unset($output[$key]);
                    }
                    break;
                case 'text':
                    if (array_key_exists($key, $input) && empty($input[$key])) {
                        unset($output[$key]);
                    }
                    break;
                default:
                    if (array_key_exists($key, $input) && empty($input[$key]) || (array_key_exists($key, $output) && !array_key_exists($key, $input))) {
                        unset($output[$key]);
                    }
                    break;
            }
            } else {
                if (empty($input[$key])) {
                    unset($output[$key]);
                }
            }
        }

        if ($doing_section) {
            add_settings_error('smartpay-notices', '', __('Settings updated.', 'wp-smartpay'), 'updated');
        }

        return $output;
    }

    private function _registered_settings_types($filtered_tab = false, $filtered_section = false)
    {
        $settings      = $this->registered_settings();
        $setting_types = array();
        foreach ($settings as $tab_id => $tab) {
            if (false !== $filtered_tab && $filtered_tab !== $tab_id) {
                continue;
            }

            foreach ($tab as $section_id => $section_or_setting) {

            // See if we have a setting registered at the tab level for backwards compatibility
                if (false !== $filtered_section && is_array($section_or_setting) && array_key_exists('type', $section_or_setting)) {
                    $setting_types[$section_or_setting['id']] = $section_or_setting['type'];
                    continue;
                }

                if (false !== $filtered_section && $filtered_section !== $section_id) {
                    continue;
                }

                foreach ($section_or_setting as $section => $section_settings) {
                    if (!empty($section_settings['type'])) {
                        $setting_types[$section_settings['id']] = $section_settings['type'];
                    }
                }
            }
        }

        return $setting_types;
    }

    public function settings_missing_callback($args)
    {
        printf(
            __('The callback function used for the %s setting is missing.', 'wp-smartpay'),
            '<strong>' . $args['id'] . '</strong>'
        );
    }
}
