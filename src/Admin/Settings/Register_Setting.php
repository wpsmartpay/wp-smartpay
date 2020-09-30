<?php

namespace SmartPay\Admin\Settings;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class Register_Setting
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Register_Setting class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Main Register_Setting Instance.
     *
     * Ensures that only one instance of Register_Setting exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Register_Setting
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Register_Setting)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add all settings sections and fields
     *
     * @since 0.0.1
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
                        method_exists($this, 'settings_' . $args['type'] . '_callback') ? [$this, 'settings_' . $args['type'] . '_callback'] : [$this, 'settings_missing_callback'],
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
     * @since 0.0.1
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
                            'name' => '<h4 class="text-uppercase text-info my-1">' . __('Pages Settings', 'smartpay') . '</h4>',
                            'desc' => __('SmartPay products uses the pages below for handling the display of checkout, payment confirmation, payment history, and payment failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'smartpay'),
                            'type' => 'header',
                        ),
                        // 'payment_page' => array(
                        //     'id'          => 'payment_page',
                        //     'name'        => __('Payment Page', 'smartpay'),
                        //     'desc'        => __(''),
                        //     'type'        => 'page_select',
                        //     'chosen'      => true,
                        //     'placeholder' => __('Select a page', 'smartpay'),
                        // ),
                        'payment_success_page' => array(
                            'id'          => 'payment_success_page',
                            'name'        => __('Payment Success Page', 'smartpay'),
                            'desc'        => __('The page customers are sent to after completing a payment. The shortcode [smartpay_payment_receipt] needs to be on this page. Output configured in the Payment Confirmation settings. This page should be excluded from any site caching.', 'smartpay'),
                            'type'        => 'page_select',
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'smartpay'),
                        ),
                        'payment_failure_page' => array(
                            'id'          => 'payment_failure_page',
                            'name'        => __('Payment Failure Page', 'smartpay'),
                            'desc'        => __('The page customers are sent to after a failed payment.', 'smartpay'),
                            'type'        => 'page_select',
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'smartpay'),
                        ),
                        'currency_settings' => array(
                            'id'   => 'currency_settings',
                            'name' => '<h4 class="text-uppercase text-info my-1">' . __('Currency Settings', 'smartpay') . '</h4>',
                            'desc' => '',
                            'type' => 'header',
                            'tooltip_title' => __('Page Settings', 'smartpay'),
                            'tooltip_desc'  => __('SmartPay products uses the pages below for handling the display of checkout, payment confirmation, payment history, and payment failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'smartpay'),
                        ),
                        'currency' => array(
                            'id'      => 'currency',
                            'name'    => __('Currency', 'smartpay'),
                            'desc'    => __('Choose your currency. Note that some payment gateways have currency restrictions.', 'smartpay'),
                            'type'    => 'select_currency',
                            'chosen'  => true,
                        ),
                        'currency_position' => array(
                            'id'      => 'currency_position',
                            'name'    => __('Currency Position', 'smartpay'),
                            'desc'    => __('Choose the location of the currency sign.', 'smartpay'),
                            'type'    => 'select',
                            'options' => array(
                                'before' => __('Before - $10', 'smartpay'),
                                'after'  => __('After - 10$', 'smartpay'),
                            ),
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
                            'name' => __('Test Mode', 'smartpay'),
                            'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'smartpay'),
                            'type' => 'switch',
                        ),
                        'gateways' => array(
                            'id'      => 'gateways',
                            'name'    => __('Payment Gateways', 'smartpay'),
                            'desc'    => __('Choose the payment gateways you want to enable.', 'smartpay'),
                            'type'    => 'gateways',
                            'options' => smartpay_payment_gateways(),
                        ),
                        'default_gateway' => array(
                            'id'      => 'default_gateway',
                            'name'    => __('Default Gateway', 'smartpay'),
                            'desc'    => __('This gateway will be loaded automatically with the checkout page.', 'smartpay'),
                            'type'    => 'gateway_select',
                            'options' => smartpay_get_enabled_payment_gateways(),
                            'std'     => 'paddle',
                        ),
                    ),
                )
            ),
            /** Emails Settings */
            'emails' => apply_filters(
                'smartpay_settings_emails',
                array(
                    'main' => array(
                        'email_settings' => array(
                            'id'   => 'email_settings',
                            'name' => '<h4 class="text-uppercase text-info my-1">' . __('General', 'smartpay') . '</h4>',
                            'type' => 'header',
                        ),
                        'from_name' => array(
                            'id'    => 'from_name',
                            'name'  => __('Form Name', 'smartpay'),
                            'desc'  => __('The name purchase receipts are said to come from. This should probably be your site or shop name.', 'smartpay'),
                            'type'  => 'text',
                        ),
                        'from_email' => array(
                            'id'    => 'from_email',
                            'name'  => __('From Email', 'smartpay'),
                            'desc'  => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'smartpay'),
                            'type'  => 'text'
                        ),
                        'purchase_email_settings' => array(
                            'id'   => 'purchase_email_settings',
                            'name' => '<h4 class="text-uppercase text-info my-1">' . __('Purchase Email', 'smartpay') . '</h4>',
                            'type' => 'header',
                        ),
                        'payment_email_subject' => array(
                            'id'    => 'payment_email_subject',
                            'name'  => __('Purchase Email Subject', 'smartpay'),
                            'desc'  => __('Enter the subject line for the purchase receipt email.', 'smartpay'),
                            'type'  => 'text'
                        ),
                        'payment_email_heading' => array(
                            'id'    => 'payment_email_heading',
                            'name'  => __('Purchase Email Heading', 'smartpay'),
                            'desc'  => __('Enter the heading for the purchase receipt email.', 'smartpay'),
                            'type'  => 'text'
                        ),
                    ),
                )
            ),
            /** License Settings */
            'extensions' => apply_filters(
                'smartpay_settings_extensions',
                array(
                    'main' => array(),
                )
            ),
            /** License Settings */
            // 'licenses' => apply_filters(
            //     'smartpay_settings_licenses',
            //     array(
            //         'main' => array(),
            //     )
            // ),
        );

        return apply_filters('smartpay_settings', $smartpay_settings);
    }

    /**
     * Retrieve settings tabs
     *
     * @since 0.0.1
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
     * @since 0.0.1
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
                'main'  => __('General', 'smartpay'),
            )),
            'gateways'  => apply_filters('smartpay_settings_sections_gateways', array(
                'main'  => __('General', 'smartpay'),
            )),
            'emails'    => apply_filters('smartpay_settings_sections_emails', array(
                'main'  => __('General', 'smartpay'),
            )),
            'extensions'  => apply_filters('smartpay_settings_sections_extensions', array(
                'main'  => __('General', 'smartpay'),
            )),
            // 'licenses'  => apply_filters('smartpay_settings_sections_licenses', array(
            //     'main'  => __('General', 'smartpay'),
            // )),
        );

        return apply_filters('smartpay_settings_sections', $sections);
    }

    public static function settings_tabs()
    {
        $tabs = array();
        $tabs['general']  = __('General', 'smartpay');
        $tabs['gateways'] = __('Payment Gateways', 'smartpay');
        $tabs['emails']   = __('Emails', 'smartpay');
        $tabs['extensions']   = __('Extensions', 'smartpay');
        // $tabs['licenses']   = __('Licenses', 'smartpay');

        return apply_filters('smartpay_settings_tabs', $tabs);
    }

    /**
     * Settings Sanitization
     *
     * Adds a settings error (for the updated message)
     * At some point this will validate input
     *
     * @since 0.0.1
     *
     * @param array $input The value inputted in the field
     * @global array $smartpay_options Array of all the SmartPay Options
     *
     * @return string $input Sanitized value
     */
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
                $output[$key] = apply_filters('smartpay_settings_sanitize_' . $type, $output[$key], $key);
                $output[$key] = apply_filters('smartpay_settings_sanitize', $output[$key], $key);
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
            add_settings_error('smartpay-notices', '', __('Settings updated.', 'smartpay'), 'updated');
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

    public function settings_header_callback($args)
    {
        // echo apply_filters('smartpay_after_setting_output', '', $args);
    }

    public function settings_select_currency_callback($args)
    {
        $currencies = [];

        foreach (smartpay_get_currencies() as $key => $currency) {

            $currencies[$key] = sprintf("%1s %2s", $currency['name'], $currency['symbol'] ? '(' . $currency['symbol'] . ')' : '');
        }
        $args['options'] = $currencies;

        echo $this->settings_select_callback($args);
    }

    public function settings_select_callback($args)
    {
        $old_value = smartpay_get_option($args['id']) ?? 0;

        if ($old_value) {
            $value = $old_value;
        } else {

            // Properly set default fallback if the Select Field allows Multiple values
            if (empty($args['multiple'])) {
                $value = isset($args['std']) ? $args['std'] : '';
            } else {
                $value = !empty($args['std']) ? $args['std'] : array();
            }
        }

        if (isset($args['placeholder'])) {
            $placeholder = $args['placeholder'];
        } else {
            $placeholder = '';
        }

        $class = $this->settings_sanitize_html_class($args['field_class']);

        if (isset($args['chosen'])) {
            $class .= ' smartpay-select-chosen';
        }

        $nonce = isset($args['data']['nonce'])
            ? ' data-nonce="' . sanitize_text_field($args['data']['nonce']) . '" '
            : '';

        // If the Select Field allows Multiple values, save as an Array
        $name_attr = 'smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']';
        $name_attr = ($args['multiple']) ? $name_attr . '[]' : $name_attr;

        $html = '<select ' . $nonce . ' id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html($placeholder) . '" ' . (($args['multiple']) ? 'multiple="true"' : '') . '>';

        foreach ($args['options'] as $option => $name) {
            if (!$args['multiple']) {
                $selected = selected($option, $value, false);
                $html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($name) . '</option>';
            } else {
                $html .= '<option value="' . esc_attr($option) . '" ' . ((in_array($option, $value)) ? 'selected="true"' : '') . '>' . esc_html($name) . '</option>';
            }
        }

        $html .= '</select>';
        $html .= '<small class="form-text text-muted" for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</small>';

        echo $html;
    }

    public function settings_gateway_select_callback($args)
    {
        $smartpay_option = smartpay_get_option($args['id']);

        $class = sanitize_html_class($args['field_class']);

        $html = '';

        $html .= '<select name="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"" id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']" class="' . $class . '">';
        $html .= '<option value="" disabled selected>Select a gateway</option>';
        foreach ($args['options'] as $key => $option) :
            $selected = isset($smartpay_option) ? selected($key, $smartpay_option, false) : '';
            $html .= '<option value="' . smartpay_sanitize_key($key) . '"' . $selected . '>' . esc_html($option['admin_label']) . '</option>';
        endforeach;

        $html .= '</select>';
        $html .= '<label for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"></label>';
        $html    .= '<small class="form-text text-muted">' . wp_kses_post($args['desc']) . '</small>';

        echo $html;
    }

    public function settings_checkbox_callback($args)
    {
        $smartpay_option = smartpay_get_option($args['id']);

        if (isset($args['faux']) && true === $args['faux']) {
            $name = '';
        } else {
            $name = 'name="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"';
        }

        $class = sanitize_html_class($args['field_class']);

        $checked  = !empty($smartpay_option) ? checked(1, $smartpay_option, false) : '';
        $html     = '<input type="hidden"' . $name . ' value="-1" />';
        $html    .= '<input type="checkbox" id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/>';
        $html    .= '<label for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"></label>';
        $html         .= '<small class="form-text text-muted">' . wp_kses_post($args['desc']) . '</small>';
        echo apply_filters('smartpay_after_setting_output', $html, $args);
    }

    public function settings_switch_callback($args)
    {
        $smartpay_option = smartpay_get_option($args['id']);
        $label = (isset($args['label']) and !empty($args['label'])) ? $args['label'] : '';

        if (isset($args['faux']) && true === $args['faux']) {
            $name = '';
        } else {
            $name = 'name="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"';
        }

        $class = sanitize_html_class($args['field_class']);

        $checked  = !empty($smartpay_option) ? checked(1, $smartpay_option, false) : '';
        $html          = '<div class="custom-control custom-switch">';
        $html    .= '<input type="hidden"' . $name . ' value="0" />';
        $html    .= '<input type="checkbox" class="custom-control-input" id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']" ' . $name . ' value="1" ' . $checked . '>';
        $html    .= '<label class="custom-control-label" for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']">' . $label . '</label>';
        $html    .= '</div>';
        $html         .= '<small class="form-text text-muted">' . wp_kses_post($args['desc']) . '</small>';

        echo apply_filters('smartpay_after_setting_output', $html, $args);
    }

    public function settings_gateways_callback($args)
    {
        $smartpay_option = smartpay_get_option($args['id']);

        $class = sanitize_html_class($args['field_class']);

        $html = '<input type="hidden" name="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']" value="-1" />';

        foreach ($args['options'] as $key => $option) :
            if (isset($smartpay_option[$key])) {
                $enabled = '1';
            } else {
                $enabled = null;
            }

            $html .= '<input name="smartpay_settings[' . esc_attr($args['id']) . '][' . smartpay_sanitize_key($key) . ']" id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . '][' . smartpay_sanitize_key($key) . ']" class="' . $class . '" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
            $html .= '<label for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . '][' . smartpay_sanitize_key($key) . ']" style="font-style: italic;">' . esc_html($option['admin_label']) . '</label><br/>';
        endforeach;

        $url   = esc_url('https://wpsmartpay.com');
        $html .= '<small class="form-text text-muted">' . sprintf(__('Don\'t see what you need? More Payment Gateway options are available <a href="%s">here</a>.', 'smartpay'), $url) . '</small>';

        echo  $html;
    }

    public function settings_sanitize_html_class($class = '')
    {
        if (is_string($class)) {
            $class = sanitize_html_class($class);
        } elseif (is_array($class)) {
            $class = array_values(array_map('sanitize_html_class', $class));
            $class = implode(' ', array_unique($class));
        }

        return $class;
    }


    public function settings_missing_callback($args)
    {
        printf(
            __('The callback function used for the %s setting is missing.', 'smartpay'),
            '<strong>' . $args['id'] . '</strong>'
        );
    }

    public function settings_page_select_callback($args)
    {
        $selected = smartpay_get_option($args['id']) ?? 0;

        $args = array(
            'depth'                 => 0,
            'child_of'              => 0,
            'selected'              => absint($selected),
            'echo'                  => 1,
            'name'                  => 'smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']',
            'id'                    => 'smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']',
            'class'                 => null, // string
            'show_option_none'      => $args['placeholder'], // string
            'show_option_no_change' => null, // string
            'option_none_value'     => null, // string
        );

        wp_dropdown_pages($args);
        // FIXME:: Show label
    }

    public function settings_text_callback($args)
    {
        $old_value = smartpay_get_option($args['id']);

        if ($old_value) {
            $value = $old_value;
        } elseif (!empty($args['allow_blank']) && empty($old_value)) {
            $value = '';
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        if (isset($args['faux']) && true === $args['faux']) {
            $args['readonly'] = true;
            $value = isset($args['std']) ? $args['std'] : '';
            $name  = '';
        } else {
            $name = 'name="smartpay_settings[' . esc_attr($args['id']) . ']"';
        }

        $class = sanitize_html_class($args['field_class']);

        $disabled = !empty($args['disabled']) ? ' disabled="disabled"' : '';
        $readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
        $size     = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
        $html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class($size) . '-text" id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']" ' . $name . ' value="' . esc_attr(stripslashes($value)) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr($args['placeholder']) . '"/>';
        $html    .= '<small class="form-text text-muted">' . wp_kses_post($args['desc']) . '</small>';

        // $html    .= '<label for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"> '  . wp_kses_post($args['desc']) . '</label>';
        echo $html;
    }

    public function settings_custom_content_callback($args)
    {
        echo $args['content'] ?? '';
    }

    public function  settings_descriptive_text_callback($args)
    {
        $html = wp_kses_post($args['desc']);

        echo $html;
    }

    public function smartpay_sanitize_html_class($class = '')
    {
        if (is_string($class)) {
            $class = sanitize_html_class($class);
        } elseif (is_array($class)) {
            $class = array_values(array_map('sanitize_html_class', $class));
            $class = implode(' ', array_unique($class));
        }

        return $class;
    }

    public function settings_textarea_callback($args)
    {
        $old_value = smartpay_get_option($args['id']);

        if ($old_value) {
            $value = $old_value;
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $class =  $this->smartpay_sanitize_html_class($args['field_class']);

        $html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']" name="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']">' . esc_textarea(stripslashes($value)) . '</textarea>';
        $html .= '<label for="smartpay_settings[' . smartpay_sanitize_key($args['id']) . ']"> '  . wp_kses_post($args['desc']) . '</label>';

        echo $html;
    }
}
