<?php

namespace ThemesGrove\SmartPay\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Setting
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Setting class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        // add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Main Setting Instance.
     *
     * Ensures that only one instance of Setting exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Setting
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Setting)) {
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

        foreach ($this->_get_settings() as $tab => $sections) {
            foreach ($sections as $section => $settings) {

                // Check for backwards compatibility
                $section_tabs = $this->_get_settings_tab_sections($tab);
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

                    $cb_function_name = 'smartpay_' . $args['type'] . '_callback';
                    add_settings_field(
                        'smartpay_settings[' . $args['id'] . ']',
                        $args['name'],
                        function_exists($cb_function_name) ? $cb_function_name : 'smartpay_missing_callback',
                        'smartpay_settings_' . $tab . '_' . $section,
                        'smartpay_settings_' . $tab . '_' . $section,
                        $args
                    );
                }
            }
        }

        // Creates our settings in the options table
        register_setting('smartpay_settings', 'smartpay_settings', 'smartpay_settings_sanitize');
    }

    /**
     * Retrieve the array of plugin settings
     *
     * @since 1.8
     * @return array
     */
    private function _get_settings()
    {
        $smartpay_settings = array(
            /** General Settings */
            'general' => apply_filters(
                'smartpay_settings_general',
                array(
                    'main' => array(
                        'page_settings' => array(
                            'id'   => 'page_settings',
                            'name' => '<h3>' . __('Pages', 'easy-digital-downloads') . '</h3>',
                            'desc' => '',
                            'type' => 'header',
                            'tooltip_title' => __('Page Settings', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('Easy Digital Downloads uses the pages below for handling the display of checkout, purchase confirmation, purchase history, and purchase failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'easy-digital-downloads'),
                        ),
                        'purchase_page' => array(
                            'id'          => 'purchase_page',
                            'name'        => __('Primary Checkout Page', 'easy-digital-downloads'),
                            'desc'        => __('This is the checkout page where buyers will complete their purchases. The [download_checkout] shortcode must be on this page.', 'easy-digital-downloads'),
                            'type'        => 'select',
                            'options'     => edd_get_pages(),
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'easy-digital-downloads'),
                        ),
                        'success_page' => array(
                            'id'          => 'success_page',
                            'name'        => __('Success Page', 'easy-digital-downloads'),
                            'desc'        => __('This is the page buyers are sent to after completing their purchases. The [smartpay_receipt] shortcode should be on this page.', 'easy-digital-downloads'),
                            'type'        => 'select',
                            'options'     => edd_get_pages(),
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'easy-digital-downloads'),
                        ),
                        'failure_page' => array(
                            'id'          => 'failure_page',
                            'name'        => __('Failed Transaction Page', 'easy-digital-downloads'),
                            'desc'        => __('This is the page buyers are sent to if their transaction is cancelled or fails.', 'easy-digital-downloads'),
                            'type'        => 'select',
                            'options'     => edd_get_pages(),
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'easy-digital-downloads'),
                        ),
                        'purchase_history_page' => array(
                            'id'          => 'purchase_history_page',
                            'name'        => __('Purchase History Page', 'easy-digital-downloads'),
                            'desc'        => __('This page shows a complete purchase history for the current user, including download links. The [purchase_history] shortcode should be on this page.', 'easy-digital-downloads'),
                            'type'        => 'select',
                            'options'     => edd_get_pages(),
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'easy-digital-downloads'),
                        ),
                        'login_redirect_page' => array(
                            'id'          => 'login_redirect_page',
                            'name'        => __('Login Redirect Page', 'easy-digital-downloads'),
                            'desc'        => sprintf(
                                __('If a customer logs in using the [smartpay_login] shortcode, this is the page they will be redirected to. Note, this can be overridden using the redirect attribute in the shortcode like this: [smartpay_login redirect="%s"].', 'easy-digital-downloads'),
                                trailingslashit(home_url())
                            ),
                            'type'        => 'select',
                            'options'     => edd_get_pages(),
                            'chosen'      => true,
                            'placeholder' => __('Select a page', 'easy-digital-downloads'),
                        ),
                        'locale_settings' => array(
                            'id'            => 'locale_settings',
                            'name'          => '<h3>' . __('Store Location', 'easy-digital-downloads') . '</h3>',
                            'desc'          => '',
                            'type'          => 'header',
                            'tooltip_title' => __('Store Location Settings', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('Easy Digital Downloads will use the following Country and State to pre-fill fields at checkout. This will also pre-calculate any taxes defined if the location below has taxes enabled.', 'easy-digital-downloads'),
                        ),
                        'base_country' => array(
                            'id'          => 'base_country',
                            'name'        => __('Base Country', 'easy-digital-downloads'),
                            'desc'        => __('Where does your store operate from?', 'easy-digital-downloads'),
                            'type'        => 'select',
                            'options'     => edd_get_country_list(),
                            'chosen'      => true,
                            'placeholder' => __('Select a country', 'easy-digital-downloads'),
                            'data'        => array(
                                'nonce' => wp_create_nonce('smartpay-country-field-nonce')
                            )
                        ),
                        'tracking_settings' => array(
                            'id'   => 'tracking_settings',
                            'name' => '<h3>' . __('Tracking', 'easy-digital-downloads') . '</h3>',
                            'desc' => '',
                            'type' => 'header',
                        ),
                        'allow_tracking' => array(
                            'id'   => 'allow_tracking',
                            'name' => __('Allow Usage Tracking?', 'easy-digital-downloads'),
                            'desc' => sprintf(
                                __('Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a discount to the smartpay shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'easy-digital-downloads'),
                                'https://easydigitaldownloads.com/downloads/?utm_source=' . substr(md5(get_bloginfo('name')), 0, 10) . '&utm_medium=admin&utm_term=settings&utm_campaign=smartpayUsageTracking'
                            ),
                            'type' => 'checkbox',
                        ),
                    ),
                    'currency' => array(
                        'currency' => array(
                            'id'      => 'currency',
                            'name'    => __('Currency', 'easy-digital-downloads'),
                            'desc'    => __('Choose your currency. Note that some payment gateways have currency restrictions.', 'easy-digital-downloads'),
                            'type'    => 'select',
                            'options' => edd_get_currencies(),
                            'chosen'  => true,
                        ),
                        'currency_position' => array(
                            'id'      => 'currency_position',
                            'name'    => __('Currency Position', 'easy-digital-downloads'),
                            'desc'    => __('Choose the location of the currency sign.', 'easy-digital-downloads'),
                            'type'    => 'select',
                            'options' => array(
                                'before' => __('Before - $10', 'easy-digital-downloads'),
                                'after'  => __('After - 10$', 'easy-digital-downloads'),
                            ),
                        ),
                        'thousands_separator' => array(
                            'id'   => 'thousands_separator',
                            'name' => __('Thousands Separator', 'easy-digital-downloads'),
                            'desc' => __('The symbol (usually , or .) to separate thousands.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'size' => 'small',
                            'std'  => ',',
                        ),
                        'decimal_separator' => array(
                            'id'   => 'decimal_separator',
                            'name' => __('Decimal Separator', 'easy-digital-downloads'),
                            'desc' => __('The symbol (usually , or .) to separate decimal points.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'size' => 'small',
                            'std'  => '.',
                        ),
                    ),
                    'api' => array(
                        'api_settings' => array(
                            'id'   => 'api_settings',
                            'name' => '<h3>' . __('API', 'easy-digital-downloads') . '</h3>',
                            'desc' => '',
                            'type' => 'header',
                            'tooltip_title' => __('API Settings', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('The Easy Digital Downloads REST API provides access to store data through our API endpoints. Enable this setting if you would like all user accounts to be able to generate their own API keys.', 'easy-digital-downloads'),
                        ),
                        'api_allow_user_keys' => array(
                            'id'   => 'api_allow_user_keys',
                            'name' => __('Allow User Keys', 'easy-digital-downloads'),
                            'desc' => __("Check this box to allow all users to generate API keys. Users with the 'manage_shop_settings' capability are always allowed to generate keys.", 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'api_help' => array(
                            'id'   => 'api_help',
                            'desc' => sprintf(__('Visit the <a href="%s" target="_blank">REST API documentation</a> for further information.', 'easy-digital-downloads'), 'http://docs.easydigitaldownloads.com/article/1131-smartpay-rest-api-introduction'),
                            'type' => 'descriptive_text',
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
                            'name' => __('Test Mode', 'easy-digital-downloads'),
                            'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'gateways' => array(
                            'id'      => 'gateways',
                            'name'    => __('Payment Gateways', 'easy-digital-downloads'),
                            'desc'    => __('Choose the payment gateways you want to enable.', 'easy-digital-downloads'),
                            'type'    => 'gateways',
                            'options' => smartpay_get_payment_gateways(),
                        ),
                        'default_gateway' => array(
                            'id'      => 'default_gateway',
                            'name'    => __('Default Gateway', 'easy-digital-downloads'),
                            'desc'    => __('This gateway will be loaded automatically with the checkout page.', 'easy-digital-downloads'),
                            'type'    => 'gateway_select',
                            'options' => smartpay_get_payment_gateways(),
                        ),
                        'accepted_cards' => array(
                            'id'      => 'accepted_cards',
                            'name'    => __('Accepted Payment Method Icons', 'easy-digital-downloads'),
                            'desc'    => __('Display icons for the selected payment methods.', 'easy-digital-downloads') . '<br/>' . __('You will also need to configure your gateway settings if you are accepting credit cards.', 'easy-digital-downloads'),
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
                            'name' => '<strong>' . __('SendWP Settings', 'easy-digital-downloads') . '</strong>',
                            'type' => 'header',
                        ),
                        'sendwp' => array(
                            'id'      => 'sendwp',
                            'name'    => __('Connection Status', 'easy-digital-downloads'),
                            'desc'    => '<p>' . __('Looking for a reliable, affordable way to deliver important emails to your customers? Try <a href="https://sendwp.com" target="_blank" rel="noopener noreferrer">SendWP</a>.', 'easy-digital-downloads') . '</p><p>' . __('For more information on this paid service, see the <a href="https://docs.easydigitaldownloads.com/article/2143-sendwp-email-delivery" target="_blank" rel="noopener noreferrer">documentation</a>.', 'easy-digital-downloads') . '</p>',
                            'type'    => 'sendwp',
                        ),
                        'email_header' => array(
                            'id'   => 'email_header',
                            'name' => '<strong>' . __('Email Configuration', 'easy-digital-downloads') . '</strong>',
                            'type' => 'header',
                        ),
                        'email_template' => array(
                            'id'      => 'email_template',
                            'name'    => __('Template', 'easy-digital-downloads'),
                            'desc'    => __('Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads'),
                            'type'    => 'select',
                            'options' => smartpay_get_email_templates(),
                        ),
                        'email_logo' => array(
                            'id'   => 'email_logo',
                            'name' => __('Logo', 'easy-digital-downloads'),
                            'desc' => __('Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads'),
                            'type' => 'upload',
                        ),
                        'from_name' => array(
                            'id'   => 'from_name',
                            'name' => __('From Name', 'easy-digital-downloads'),
                            'desc' => __('The name purchase receipts are said to come from. This should probably be your site or shop name.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => get_bloginfo('name'),
                        ),
                        'from_email' => array(
                            'id'   => 'from_email',
                            'name' => __('From Email', 'easy-digital-downloads'),
                            'desc' => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'easy-digital-downloads'),
                            'type' => 'email',
                            'std'  => get_bloginfo('admin_email'),
                        ),
                        'email_settings' => array(
                            'id'   => 'email_settings',
                            'name' => '',
                            'desc' => '',
                            'type' => 'hook',
                        ),
                        'advanced_emails_header' => array(
                            'id'   => 'advanced_emails_header',
                            'name' => '<strong>' . __('Advanced emails', 'easy-digital-downloads') . '</strong>',
                            'type' => 'header',
                        ),
                        'jilt'                   => array(
                            'id'   => 'jilt',
                            'name' => __('Enhanced emails via Jilt', 'easy-digital-downloads'),
                            'desc' => '<p>' . __('Create beautiful transactional, automated, and marketing emails using a drag-and-drop editor with <a href="https://jilt.com/?utm_source=smartpay-core&utm_medium=referral&utm_campaign=smartpay-enhanced-emails" target="_blank" rel="noopener noreferrer">Jilt</a>.', 'easy-digital-downloads') . '</p><p>' . __('Learn more about free and paid plans in the <a href="https://docs.easydigitaldownloads.com/article/2199-jilt-overview" target="_blank" rel="noopener noreferrer">documentation</a>.', 'easy-digital-downloads') . '</p>',
                            'type' => 'jilt',
                        ),
                    ),
                    'purchase_receipts' => array(
                        'purchase_receipt_email_settings' => array(
                            'id'   => 'purchase_receipt_email_settings',
                            'name' => '',
                            'desc' => '',
                            'type' => 'hook',
                        ),
                        'purchase_subject' => array(
                            'id'   => 'purchase_subject',
                            'name' => __('Purchase Email Subject', 'easy-digital-downloads'),
                            'desc' => __('Enter the subject line for the purchase receipt email.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('Purchase Receipt', 'easy-digital-downloads'),
                        ),
                        'purchase_heading' => array(
                            'id'   => 'purchase_heading',
                            'name' => __('Purchase Email Heading', 'easy-digital-downloads'),
                            'desc' => __('Enter the heading for the purchase receipt email.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('Purchase Receipt', 'easy-digital-downloads'),
                        ),
                        'purchase_receipt' => array(
                            'id'   => 'purchase_receipt',
                            'name' => __('Purchase Receipt', 'easy-digital-downloads'),
                            'desc' => __('Enter the text that is sent as purchase receipt email to users after completion of a successful purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads') . '<br/>' . smartpay_get_emails_tags_list(),
                            'type' => 'rich_editor',
                            'std'  => __("Dear", "easy-digital-downloads") . " {name},\n\n" . __("Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads") . "\n\n{download_list}\n\n{sitename}",
                        ),
                    ),
                    'sale_notifications' => array(
                        'sale_notification_subject' => array(
                            'id'   => 'sale_notification_subject',
                            'name' => __('Sale Notification Subject', 'easy-digital-downloads'),
                            'desc' => __('Enter the subject line for the sale notification email.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => 'New download purchase - Order #{payment_id}',
                        ),
                        'sale_notification_heading' => array(
                            'id'   => 'sale_notification_heading',
                            'name' => __('Sale Notification Heading', 'easy-digital-downloads'),
                            'desc' => __('Enter the heading for the sale notification email.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('New Sale!', 'easy-digital-downloads'),
                        ),
                        'sale_notification' => array(
                            'id'   => 'sale_notification',
                            'name' => __('Sale Notification', 'easy-digital-downloads'),
                            'desc' => __('Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads') . '<br/>' . smartpay_get_emails_tags_list(),
                            'type' => 'rich_editor',
                            'std'  => smartpay_get_default_sale_notification_email(),
                        ),
                        'admin_notice_emails' => array(
                            'id'   => 'admin_notice_emails',
                            'name' => __('Sale Notification Emails', 'easy-digital-downloads'),
                            'desc' => __('Enter the email address(es) that should receive a notification anytime a sale is made, one per line.', 'easy-digital-downloads'),
                            'type' => 'textarea',
                            'std'  => get_bloginfo('admin_email'),
                        ),
                        'disable_admin_notices' => array(
                            'id'   => 'disable_admin_notices',
                            'name' => __('Disable Admin Notifications', 'easy-digital-downloads'),
                            'desc' => __('Check this box if you do not want to receive sales notification emails.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                    ),
                )
            ),
            /** Styles Settings */
            'styles' => apply_filters(
                'smartpay_settings_styles',
                array(
                    'main' => array(
                        'disable_styles' => array(
                            'id'            => 'disable_styles',
                            'name'          => __('Disable Styles', 'easy-digital-downloads'),
                            'desc'          => __('Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'easy-digital-downloads'),
                            'type'          => 'checkbox',
                            'tooltip_title' => __('Disabling Styles', 'easy-digital-downloads'),
                            'tooltip_desc'  => __("If your theme has a complete custom CSS file for Easy Digital Downloads, you may wish to disable our default styles. This is not recommended unless you're sure your theme has a complete custom CSS.", 'easy-digital-downloads'),
                        ),
                        'button_header' => array(
                            'id'   => 'button_header',
                            'name' => '<strong>' . __('Buttons', 'easy-digital-downloads') . '</strong>',
                            'desc' => __('Options for add to cart and purchase buttons', 'easy-digital-downloads'),
                            'type' => 'header',
                        ),
                        'button_style' => array(
                            'id'      => 'button_style',
                            'name'    => __('Default Button Style', 'easy-digital-downloads'),
                            'desc'    => __('Choose the style you want to use for the buttons.', 'easy-digital-downloads'),
                            'type'    => 'select',
                            'options' => smartpay_get_button_styles(),
                        ),
                        'checkout_color' => array(
                            'id'      => 'checkout_color',
                            'name'    => __('Default Button Color', 'easy-digital-downloads'),
                            'desc'    => __('Choose the color you want to use for the buttons.', 'easy-digital-downloads'),
                            'type'    => 'color_select',
                            'options' => smartpay_get_button_colors(),
                        ),
                    ),
                )
            ),
            /** Taxes Settings */
            'taxes' => apply_filters(
                'smartpay_settings_taxes',
                array(
                    'main' => array(
                        'tax_help' => array(
                            'id'   => 'tax_help',
                            'name' => __('Need help?', 'easy-digital-downloads'),
                            'desc' => sprintf(__('Visit the <a href="%s" target="_blank">Tax setup documentation</a> for further information. If you need VAT support, there are options listed on the documentation page.', 'easy-digital-downloads'), 'http://docs.easydigitaldownloads.com/article/238-tax-settings'),
                            'type' => 'descriptive_text',
                        ),
                        'enable_taxes' => array(
                            'id'            => 'enable_taxes',
                            'name'          => __('Enable Taxes', 'easy-digital-downloads'),
                            'desc'          => __('Check this to enable taxes on purchases.', 'easy-digital-downloads'),
                            'type'          => 'checkbox',
                            'tooltip_title' => __('Enabling Taxes', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('With taxes enabled, Easy Digital Downloads will use the rules below to charge tax to customers. With taxes enabled, customers are required to input their address on checkout so that taxes can be properly calculated.', 'easy-digital-downloads'),
                        ),
                        'tax_rates' => array(
                            'id'   => 'tax_rates',
                            'name' => '<strong>' . __('Tax Rates', 'easy-digital-downloads') . '</strong>',
                            'desc' => __('Add tax rates for specific regions. Enter a percentage, such as 6.5 for 6.5%.', 'easy-digital-downloads'),
                            'type' => 'tax_rates',
                        ),
                        'tax_rate' => array(
                            'id'   => 'tax_rate',
                            'name' => __('Fallback Tax Rate', 'easy-digital-downloads'),
                            'desc' => __('Customers not in a specific rate will be charged this tax rate. Enter a percentage, such as 6.5 for 6.5%. ', 'easy-digital-downloads'),
                            'type' => 'text',
                            'size' => 'small',
                            'tooltip_title' => __('Fallback Tax Rate', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('If the customer\'s address fails to meet the above tax rules, you can define a `default` tax rate to be applied to all other customers. Enter a percentage, such as 6.5 for 6.5%.', 'easy-digital-downloads'),
                        ),
                        'prices_include_tax' => array(
                            'id'   => 'prices_include_tax',
                            'name' => __('Prices entered with tax', 'easy-digital-downloads'),
                            'desc' => __('This option affects how you enter prices.', 'easy-digital-downloads'),
                            'type' => 'radio',
                            'std'  => 'no',
                            'options' => array(
                                'yes' => __('Yes, I will enter prices inclusive of tax', 'easy-digital-downloads'),
                                'no'  => __('No, I will enter prices exclusive of tax', 'easy-digital-downloads'),
                            ),
                            'tooltip_title' => __('Prices Inclusive of Tax', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('When using prices inclusive of tax, you will be entering your prices as the total amount you want a customer to pay for the download, including tax. Easy Digital Downloads will calculate the proper amount to tax the customer for the defined total price.', 'easy-digital-downloads'),
                        ),
                        'display_tax_rate' => array(
                            'id'   => 'display_tax_rate',
                            'name' => __('Display Tax Rate on Prices', 'easy-digital-downloads'),
                            'desc' => __('Some countries require a notice when product prices include tax.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'checkout_include_tax' => array(
                            'id'   => 'checkout_include_tax',
                            'name' => __('Display during checkout', 'easy-digital-downloads'),
                            'desc' => __('Should prices on the checkout page be shown with or without tax?', 'easy-digital-downloads'),
                            'type' => 'select',
                            'std'  => 'no',
                            'options' => array(
                                'yes' => __('Including tax', 'easy-digital-downloads'),
                                'no'  => __('Excluding tax', 'easy-digital-downloads'),
                            ),
                            'tooltip_title' => __('Taxes Displayed for Products on Checkout', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('This option will determine whether the product price displays with or without tax on checkout.', 'easy-digital-downloads'),
                        ),
                    ),
                )
            ),
            /** Extension Settings */
            'extensions' => apply_filters(
                'smartpay_settings_extensions',
                array()
            ),
            'licenses' => apply_filters(
                'smartpay_settings_licenses',
                array()
            ),
            /** Misc Settings */
            'misc' => apply_filters(
                'smartpay_settings_misc',
                array(
                    'main' => array(
                        'redirect_on_add' => array(
                            'id'   => 'redirect_on_add',
                            'name' => __('Redirect to Checkout', 'easy-digital-downloads'),
                            'desc' => __('Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                            'tooltip_title' => __('Redirect to Checkout', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('When enabled, once an item has been added to the cart, the customer will be redirected directly to your checkout page. This is useful for stores that sell single items.', 'easy-digital-downloads'),
                        ),
                        'item_quantities' => array(
                            'id'   => 'item_quantities',
                            'name' => __('Cart Item Quantities', 'easy-digital-downloads'),
                            'desc' => sprintf(__('Allow quantities to be adjusted when adding %s to the cart, and while viewing the checkout cart.', 'easy-digital-downloads'), smartpay_get_label_plural(true)),
                            'type' => 'checkbox',
                        ),
                        'debug_mode' => array(
                            'id'   => 'debug_mode',
                            'name' => __('Debug Mode', 'easy-digital-downloads'),
                            'desc' => __('Check this box to enable debug mode. When enabled, debug messages will be logged and shown in Downloads &rarr; Tools &rarr; Debug Log.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'uninstall_on_delete' => array(
                            'id'   => 'uninstall_on_delete',
                            'name' => __('Remove Data on Uninstall?', 'easy-digital-downloads'),
                            'desc' => __('Check this box if you would like smartpay to completely remove all of its data when the plugin is deleted.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                    ),
                    'checkout' => array(
                        'enforce_ssl' => array(
                            'id'   => 'enforce_ssl',
                            'name' => __('Enforce SSL on Checkout', 'easy-digital-downloads'),
                            'desc' => __('Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'logged_in_only' => array(
                            'id'   => 'logged_in_only',
                            'name' => __('Require Login', 'easy-digital-downloads'),
                            'desc' => __('Require that users be logged-in to purchase files.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                            'tooltip_title' => __('Require Login', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('You can require that customers create and login to user accounts prior to purchasing from your store by enabling this option. When unchecked, users can purchase without being logged in by using their name and email address.', 'easy-digital-downloads'),
                        ),
                        'show_register_form' => array(
                            'id'      => 'show_register_form',
                            'name'    => __('Show Register / Login Form?', 'easy-digital-downloads'),
                            'desc'    => __('Display the registration and login forms on the checkout page for non-logged-in users.', 'easy-digital-downloads'),
                            'type'    => 'select',
                            'std'     => 'none',
                            'options' => array(
                                'both'         => __('Registration and Login Forms', 'easy-digital-downloads'),
                                'registration' => __('Registration Form Only', 'easy-digital-downloads'),
                                'login'        => __('Login Form Only', 'easy-digital-downloads'),
                                'none'         => __('None', 'easy-digital-downloads'),
                            ),
                        ),
                        'allow_multiple_discounts' => array(
                            'id'   => 'allow_multiple_discounts',
                            'name' => __('Multiple Discounts', 'easy-digital-downloads'),
                            'desc' => __('Allow customers to use multiple discounts on the same purchase?', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'enable_cart_saving' => array(
                            'id'   => 'enable_cart_saving',
                            'name' => __('Enable Cart Saving', 'easy-digital-downloads'),
                            'desc' => __('Check this to enable cart saving on the checkout.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                            'tooltip_title' => __('Cart Saving', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('Cart saving allows shoppers to create a temporary link to their current shopping cart so they can come back to it later, or share it with someone.', 'easy-digital-downloads'),
                        ),
                    ),
                    'button_text' => array(
                        'checkout_label' => array(
                            'id'   => 'checkout_label',
                            'name' => __('Complete Purchase Text', 'easy-digital-downloads'),
                            'desc' => __('The button label for completing a purchase.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('Purchase', 'easy-digital-downloads'),
                        ),
                        'free_checkout_label' => array(
                            'id'   => 'free_checkout_label',
                            'name' => __('Complete Free Purchase Text', 'easy-digital-downloads'),
                            'desc' => __('The button label for completing a free purchase.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('Free Download', 'easy-digital-downloads'),
                        ),
                        'add_to_cart_text' => array(
                            'id'   => 'add_to_cart_text',
                            'name' => __('Add to Cart Text', 'easy-digital-downloads'),
                            'desc' => __('Text shown on the Add to Cart Buttons.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('Add to Cart', 'easy-digital-downloads'),
                        ),
                        'checkout_button_text' => array(
                            'id'   => 'checkout_button_text',
                            'name' => __('Checkout Button Text', 'easy-digital-downloads'),
                            'desc' => __('Text shown on the Add to Cart Button when the product is already in the cart.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => _x('Checkout', 'text shown on the Add to Cart Button when the product is already in the cart', 'easy-digital-downloads'),
                        ),
                        'buy_now_text' => array(
                            'id'   => 'buy_now_text',
                            'name' => __('Buy Now Text', 'easy-digital-downloads'),
                            'desc' => __('Text shown on the Buy Now Buttons.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'std'  => __('Buy Now', 'easy-digital-downloads'),
                        ),
                    ),
                    'file_downloads' => array(
                        'download_method' => array(
                            'id'      => 'download_method',
                            'name'    => __('Download Method', 'easy-digital-downloads'),
                            'desc'    => sprintf(__('Select the file download method. Note, not all methods work on all servers.', 'easy-digital-downloads'), smartpay_get_label_singular()),
                            'type'    => 'select',
                            'tooltip_title' => __('Download Method', 'easy-digital-downloads'),
                            'tooltip_desc' => __('Due to its consistency in multiple platforms and better file protection, \'forced\' is the default method. Because Easy Digital Downloads uses PHP to process the file with the \'forced\' method, larger files can cause problems with delivery, resulting in hitting the \'max execution time\' of the server. If users are getting 404 or 403 errors when trying to access their purchased files when using the \'forced\' method, changing to the \'redirect\' method can help resolve this.', 'easy-digital-downloads'),
                            'options' => array(
                                'direct'   => __('Forced', 'easy-digital-downloads'),
                                'redirect' => __('Redirect', 'easy-digital-downloads'),
                            ),
                        ),
                        'symlink_file_downloads' => array(
                            'id'   => 'symlink_file_downloads',
                            'name' => __('Symlink File Downloads?', 'easy-digital-downloads'),
                            'desc' => __('Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'file_download_limit' => array(
                            'id'   => 'file_download_limit',
                            'name' => __('File Download Limit', 'easy-digital-downloads'),
                            'desc' => sprintf(__('The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'easy-digital-downloads'), smartpay_get_label_singular()),
                            'type' => 'number',
                            'size' => 'small',
                            'tooltip_title' => __('File Download Limits', 'easy-digital-downloads'),
                            'tooltip_desc'  => sprintf(__('Set the global default for the number of times a customer can download items they purchase. Using a value of 0 is unlimited. This can be defined on a %s-specific level as well. Download limits can also be reset for an individual purchase.', 'easy-digital-downloads'), smartpay_get_label_singular(true)),
                        ),
                        'download_link_expiration' => array(
                            'id'            => 'download_link_expiration',
                            'name'          => __('Download Link Expiration', 'easy-digital-downloads'),
                            'desc'          => __('How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'easy-digital-downloads'),
                            'tooltip_title' => __('Download Link Expiration', 'easy-digital-downloads'),
                            'tooltip_desc'  => __('When a customer receives a link to their downloads via email, in their receipt, or in their purchase history, the link will only be valid for the timeframe (in hours) defined in this setting. Sending a new purchase receipt or visiting the account page will re-generate a valid link for the customer.', 'easy-digital-downloads'),
                            'type'          => 'number',
                            'size'          => 'small',
                            'std'           => '24',
                            'min'           => '0',
                        ),
                        'disable_redownload' => array(
                            'id'   => 'disable_redownload',
                            'name' => __('Disable Redownload?', 'easy-digital-downloads'),
                            'desc' => __('Check this if you do not want to allow users to redownload items from their purchase history.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                    ),
                    'accounting'     => array(
                        'enable_skus' => array(
                            'id'   => 'enable_skus',
                            'name' => __('Enable SKU Entry', 'easy-digital-downloads'),
                            'desc' => __('Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'enable_sequential' => array(
                            'id'   => 'enable_sequential',
                            'name' => __('Sequential Order Numbers', 'easy-digital-downloads'),
                            'desc' => __('Check this box to enable sequential order numbers.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'sequential_start' => array(
                            'id'   => 'sequential_start',
                            'name' => __('Sequential Starting Number', 'easy-digital-downloads'),
                            'desc' => __('The number at which the sequence should begin.', 'easy-digital-downloads'),
                            'type' => 'number',
                            'size' => 'small',
                            'std'  => '1',
                        ),
                        'sequential_prefix' => array(
                            'id'   => 'sequential_prefix',
                            'name' => __('Sequential Number Prefix', 'easy-digital-downloads'),
                            'desc' => __('A prefix to prepend to all sequential order numbers.', 'easy-digital-downloads'),
                            'type' => 'text',
                        ),
                        'sequential_postfix' => array(
                            'id'   => 'sequential_postfix',
                            'name' => __('Sequential Number Postfix', 'easy-digital-downloads'),
                            'desc' => __('A postfix to append to all sequential order numbers.', 'easy-digital-downloads'),
                            'type' => 'text',
                        ),
                    ),
                    'site_terms'     => array(
                        'show_agree_to_terms' => array(
                            'id'   => 'show_agree_to_terms',
                            'name' => __('Agree to Terms', 'easy-digital-downloads'),
                            'desc' => __('Check this to show an agree to terms on checkout that users must agree to before purchasing.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'agree_label' => array(
                            'id'   => 'agree_label',
                            'name' => __('Agree to Terms Label', 'easy-digital-downloads'),
                            'desc' => __('Label shown next to the agree to terms checkbox.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'size' => 'regular',
                        ),
                        'agree_text' => array(
                            'id'   => 'agree_text',
                            'name' => __('Agreement Text', 'easy-digital-downloads'),
                            'desc' => __('If Agree to Terms is checked, enter the agreement terms here.', 'easy-digital-downloads'),
                            'type' => 'rich_editor',
                        ),
                    ),
                )
            ),
            'privacy' => apply_filters(
                'smartpay_settings_privacy',
                array(
                    'general' => array(
                        'show_agree_to_privacy_policy' => array(
                            'id'   => 'show_agree_to_privacy_policy',
                            'name' => __('Agree to Privacy Policy', 'easy-digital-downloads'),
                            'desc' => __('Check this to show an agree to Privacy Policy on checkout that users must agree to before purchasing.', 'easy-digital-downloads'),
                            'type' => 'checkbox',
                        ),
                        'agree_privacy_label' => array(
                            'id'   => 'privacy_agree_label',
                            'name' => __('Agree to Privacy Policy Label', 'easy-digital-downloads'),
                            'desc' => __('Label shown next to the agree to Privacy Policy checkbox.', 'easy-digital-downloads'),
                            'type' => 'text',
                            'size' => 'regular',
                        ),
                        'show_privacy_policy_on_checkout' => array(
                            'id'   => 'show_privacy_policy_on_checkout',
                            'name' => __('Show the Privacy Policy on checkout', 'easy-digital-downloads'),
                            'desc' => __('Display your Privacy Policy on checkout.', 'easy-digital-downloads') . ' <a href="' . esc_attr(admin_url('privacy.php')) . '">' . __('Set your Privacy Policy here', 'easy-digital-downloads') . '</a>.',
                            'type' => 'checkbox',
                        ),
                    ),
                    'export_erase' => array()
                )
            )
        );

        return apply_filters('smartpay_settings', $smartpay_settings);
    }

    /**
     * Retrieve settings tabs
     *
     * @since 2.5
     * @return array $section
     */
    private function _get_settings_tab_sections($tab = false)
    {
        $tabs     = array();
        $sections = $this->_get_registered_settings_sections();

        if ($tab && !empty($sections[$tab])) {
            $tabs = $sections[$tab];
        } else if ($tab) {
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
    private function _get_registered_settings_sections()
    {
        static $sections = false;

        if (false !== $sections) {
            return $sections;
        }

        $sections = array(
            'general'   => apply_filters('smartpay_settings_sections_general', []),
            'gateways'  => apply_filters('smartpay_settings_sections_gateways', []),
            'emails'    => apply_filters('smartpay_settings_sections_emails', []),
            'license'   => apply_filters('smartpay_settings_sections_licenses', []),
        );

        return apply_filters('smartpay_settings_sections', $sections);
    }
}