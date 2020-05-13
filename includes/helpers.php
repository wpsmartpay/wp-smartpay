<?php

require_once __DIR__ . '/helpers/gateway.php';
require_once __DIR__ . '/helpers/payment.php';
require_once __DIR__ . '/helpers/product.php';

use SmartPay\Models\SmartPay_Payment;
use SmartPay\Payment;

function smartpay_view(string $file, array $data = [])
{
    $file = SMARTPAY_DIR . '/includes/views/' . $file . '.php';
    if (file_exists($file)) {
        if (count($data)) {
            extract($data);
        }

        require_once $file;
    } else {
        wp_die('View not found');
    }
}

function smartpay_view_render(string $file, array $data = [])
{
    $file = SMARTPAY_DIR . '/includes/views/' . $file . '.php';
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

function smartpay_get_settings()
{
    $settings = get_option('smartpay_settings');

    if (empty($settings)) {
        $general_settings = get_option('smartpay_settings_general') ? get_option('smartpay_settings_general') : [];
        $gateway_settings = get_option('smartpay_settings_gateways') ? get_option('smartpay_settings_gateways') : [];
        $email_settings   = get_option('smartpay_settings_emails') ? get_option('smartpay_settings_emails') : [];
        $license_settings = get_option('smartpay_settings_licenses') ? get_option('smartpay_settings_licenses') : [];

        $settings = array_merge($general_settings, $gateway_settings, $email_settings, $license_settings);
        update_option('smartpay_settings', $settings);
    }
    return apply_filters('smartpay_get_settings', $settings);
}

function smartpay_get_option($key = '', $default = false)
{
    global $smartpay_options;
    $value = !empty($smartpay_options[$key]) ? $smartpay_options[$key] : $default;
    return $value;
}

function smartpay_get_currency()
{
    $currency = smartpay_get_option('currency', 'USD');
    return $currency;
}

function smartpay_get_currency_symbol($currency = '')
{
    if (empty($currency)) {
        $currency = smartpay_get_currency();
    }

    $currencies = smartpay_get_currencies();

    if (array_key_exists($currency, $currencies)) {
        return $currencies[$currency]['symbol'] ?? '&#36;';
    } else {
        return $currencies['USD']['symbol'] ?? '&#36;';
    }
}

function smartpay_get_currencies()
{
    // TODO:: Update currency text
    return array(
        'USD'        => [
            'name'   => esc_html__('USD'),
            'symbol' => '&#36;',
        ],
        'EUR'        => [
            'name'   => esc_html__('EUR'),
            'symbol' => '&euro;',
        ],
        'GBP'        => [
            'name'   => esc_html__('GBP'),
            'symbol' => '&pound;',
        ],
        'ARS'        => [
            'name'   => esc_html__('ARS'),
            'symbol' => '',
        ],
        'AUD'        => [
            'name'   => esc_html__('AUD'),
            'symbol' => '&#36;',
        ],
        'BRL'        => [
            'name'   => esc_html__('BRL'),
            'symbol' => 'R&#36;',
        ],
        'CAD'        => [
            'name'   => esc_html__('CAD'),
            'symbol' => '&#36;',
        ],
        'CHF'        => [
            'name'   => esc_html__('CHF'),
            'symbol' => '',
        ],
        'CNY'        => [
            'name'   => esc_html__('CNY'),
            'symbol' => '',
        ],
        'CZK'        => [
            'name'   => esc_html__('CZK'),
            'symbol' => '',
        ],
        'DKK'        => [
            'name'   => esc_html__('DKK'),
            'symbol' => '',
        ],
        'HKD'        => [
            'name'   => esc_html__('HKD'),
            'symbol' => '&#36;',
        ],
        'HUF'        => [
            'name'   => esc_html__('HUF'),
            'symbol' => '',
        ],
        'INR'        => [
            'name'   => esc_html__('INR'),
            'symbol' => '',
        ],
        'JPY'        => [
            'name'   => esc_html__('JPY'),
            'symbol' => '&yen;',
        ],
        'KRW'        => [
            'name'   => esc_html__('KRW'),
            'symbol' => '',
        ],
        'MXN'        => [
            'name'   => esc_html__('MXN'),
            'symbol' => '&#36;',
        ],
        'NZD'        => [
            'name'   => esc_html__('NZD'),
            'symbol' => '&#36;',
        ],
        'PLN'        => [
            'name'   => esc_html__('PLN'),
            'symbol' => '',
        ],
        'RUB'        => [
            'name'   => esc_html__('RUB'),
            'symbol' => '',
        ],
        'SEK'        => [
            'name'   => esc_html__('SEK'),
            'symbol' => '',
        ],
        'SGD'        => [
            'name'   => esc_html__('SGD'),
            'symbol' => '&#36;',
        ],
        'TWD'        => [
            'name'   => esc_html__('TWD'),
            'symbol' => '',
        ],
        'ZAR'        => [
            'name'   => esc_html__('ZAR'),
            'symbol' => '',
        ],
        'BDT'        => [
            'name'   => esc_html__('Bangladeshi taka'),
            'symbol' => '&#2547;',
        ],
    );
}

function smartpay_amount_format($amount, $currency = '')
{
    if (empty($currency)) {
        $currency = smartpay_get_option('currency', 'USD');
    }

    $symbol = smartpay_get_currency_symbol($currency);

    $position = smartpay_get_option('currency_position', 'before');

    $amount = abs($amount);

    if ($position == 'before') {
        switch ($currency) {
            case 'GBP':
            case 'BRL':
            case 'EUR':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'HKD':
            case 'MXN':
            case 'NZD':
            case 'SGD':
            case 'JPY':
            case 'BDT':
                $formatted = $symbol . $amount . ' ' . $currency;
                break;
            default:
                $formatted = $currency . ' ' . $amount;
                break;
        }
    } else {
        switch ($currency) {
            case 'GBP':
            case 'BRL':
            case 'EUR':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'HKD':
            case 'MXN':
            case 'SGD':
            case 'JPY':
            case 'BDT':
                $formatted = $currency . ' ' . $amount . $symbol;
                break;
            default:
                $formatted = $amount . ' ' . $currency;
                break;
        }
    }

    return $formatted;
}

function smartpay_die($message = '', $title = '', $status = 400)
{
    wp_die($message, $title, array('response' => $status));
}

function smartpay_get_pages()
{
    $pages = get_pages();
    if ($pages) {
        foreach ($pages as $page) {
            $pages_options[$page->ID] = $page->post_title;
        }
    }

    return $pages_options;
}

function smartpay_sanitize_key($key)
{
    $key = preg_replace('/[^a-zA-Z0-9_\-\.\:\/]/', '', $key);
    return $key;
}

function smartpay_is_test_mode()
{
    $is_test_mode = smartpay_get_option('test_mode', false);
    return (bool)  $is_test_mode;
}

function smartpay_get_svg_icon_url()
{
    // TODO: Move to css
    return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTEyIDUxMjsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0yMjQsMTU5Ljk5MnYtMzJIMzJjLTE3LjYzMiwwLTMyLDE0LjM2OC0zMiwzMnY2NGgyMzAuNzUyQzIyNi4zMDQsMjA0LjQ0LDIyNCwxODMuMzg0LDIyNCwxNTkuOTkyeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNNTEwLjY4OCwyODcuOTkyYy0yMS44MjQsMzMuNjMyLTU1LjEwNCw2Mi4yNC0xMDIuNzg0LDg5LjYzMmMtNy4zMjgsNC4xOTItMTUuNTg0LDYuMzY4LTIzLjkwNCw2LjM2OA0KCQkJcy0xNi41NzYtMi4xNzYtMjMuODA4LTYuMzA0Yy00Ny42OC0yNy40NTYtODAuOTYtNTYuMDk2LTEwMi44MTYtODkuNjk2SDB2MTYwYzAsMTcuNjY0LDE0LjM2OCwzMiwzMiwzMmg0NDgNCgkJCWMxNy42NjQsMCwzMi0xNC4zMzYsMzItMzJ2LTE2MEg1MTAuNjg4eiBNMTQ0LDM4My45OTJIODBjLTguODMyLDAtMTYtNy4xNjgtMTYtMTZjMC04LjgzMiw3LjE2OC0xNiwxNi0xNmg2NA0KCQkJYzguODMyLDAsMTYsNy4xNjgsMTYsMTZDMTYwLDM3Ni44MjQsMTUyLjgzMiwzODMuOTkyLDE0NCwzODMuOTkyeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNNTAyLjMwNCw4MS4zMDRsLTExMi00OGMtNC4wNjQtMS43MjgtOC41NzYtMS43MjgtMTIuNjQsMGwtMTEyLDQ4QzI1OS44MDgsODMuOCwyNTYsODkuNTkyLDI1Niw5NS45OTJ2NjQNCgkJCWMwLDg4LjAzMiwzMi41NDQsMTM5LjQ4OCwxMjAuMDMyLDE4OS44ODhjMi40NjQsMS40MDgsNS4yMTYsMi4xMTIsNy45NjgsMi4xMTJzNS41MDQtMC43MDQsNy45NjgtMi4xMTINCgkJCUM0NzkuNDU2LDI5OS42MDgsNTEyLDI0OC4xNTIsNTEyLDE1OS45OTJ2LTY0QzUxMiw4OS41OTIsNTA4LjE5Miw4My44LDUwMi4zMDQsODEuMzA0eiBNNDQ0LjUxMiwxNTQuMDA4bC02NCw4MA0KCQkJYy0zLjA3MiwzLjc3Ni03LjY4LDUuOTg0LTEyLjUxMiw1Ljk4NGMtMC4yMjQsMC0wLjQ4LDAtMC42NzIsMGMtNS4wODgtMC4yMjQtOS43OTItMi44NDgtMTIuNjQtNy4xMDRsLTMyLTQ4DQoJCQljLTQuODk2LTcuMzYtMi45MTItMTcuMjgsNC40NDgtMjIuMTc2YzcuMjk2LTQuODY0LDE3LjI0OC0yLjk0NCwyMi4xNzYsNC40NDhsMTkuODcyLDI5Ljc5Mmw1MC4zMDQtNjIuOTEyDQoJCQljNS41MzYtNi44OCwxNS42MTYtNy45NjgsMjIuNDk2LTIuNDk2QzQ0OC44OTYsMTM3LjAxNiw0NDkuOTg0LDE0Ny4wOTYsNDQ0LjUxMiwxNTQuMDA4eiIvPg0KCTwvZz4NCjwvZz4NCjwvc3ZnPg0K';
}

function smartpay_get_page_uri($page_id, $query_string = null)
{
    $page_uri = get_permalink($page_id);

    if ($query_string) {
        $page_uri .= $query_string;
    }

    return $page_uri;
}