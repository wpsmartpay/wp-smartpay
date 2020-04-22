<?php

function view(string $file, array $data = [])
{
    $file = WP_SMARTPAY_PATH . 'includes/views/' . $file . '.php';
    if (file_exists($file)) {

        if (count($data)) {
            extract($data);
        }

        require_once $file;
    } else {
        wp_die('View not found');
    }
}

function view_render(string $file, array $data = [])
{
    $file = WP_SMARTPAY_PATH . 'includes/views/' . $file . '.php';
    if (file_exists($file)) {

        if (count($data)) {
            extract($data);
        }

        ob_start();
        include($file);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    } else {
        wp_die('View not found');
    }
}

function gateways()
{
    $gateways = array(
        'paddle' => array(
            'admin_label'    => 'Paddle',
            'checkout_label' => 'Paddle'
        )
    );

    $gateways = apply_filters('gateways', $gateways);

    return $gateways;
}

function smartpay_missing_callback($args)
{
    printf(
        __('The callback function used for the %s setting is missing.', 'wp-smartpay'),
        '<strong>' . $args['id'] . '</strong>'
    );
}



function display_options()
{
    add_settings_section("header_section", "Header Options", "display_header_options_content", "theme-options");

    //here we display the sections and options in the settings page based on the active tab
    if (isset($_GET["tab"])) {
        if ($_GET["tab"] == "header-options") {
            add_settings_field("header_logo", "Logo Url", "display_logo_form_element", "theme-options", "header_section");
            register_setting("header_section", "header_logo");
        } else {
            add_settings_field("advertising_code", "Ads Code", "display_ads_form_element", "theme-options", "header_section");

            register_setting("header_section", "advertising_code");
        }
    } else {
        add_settings_field("header_logo", "Logo Url", "display_logo_form_element", "theme-options", "header_section");
        register_setting("header_section", "header_logo");
    }
}

function display_header_options_content()
{
    echo "The header of the theme";
}
function display_logo_form_element()
{
?>
<input type="text" name="header_logo" id="header_logo" value="<?php echo get_option('header_logo'); ?>" />
<?php
}
function display_ads_form_element()
{
?>
<input type="text" name="advertising_code" id="advertising_code"
    value="<?php echo get_option('advertising_code'); ?>" />
<?php
}

add_action("admin_init", "display_options");