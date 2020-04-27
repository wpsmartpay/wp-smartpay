<?php

use ThemesGrove\SmartPay\Gateway;
use ThemesGrove\SmartPay\Payment;
use ThemesGrove\SmartPay\Setting;

function smartpay_view(string $file, array $data = [])
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

function smartpay_view_render(string $file, array $data = [])
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

function smartpay_get_settings()
{
    return Setting::get_settings();
}

function smartpay_get_option($key = '', $default = false)
{
    global $smartpay_options;
    $value = !empty($smartpay_options[$key]) ? $smartpay_options[$key] : $default;
    return $value;
}

function smartpay_gateways()
{
    return Gateway::gateways();
}

function smartpay_get_currency_symbol($currency = 'USD')
{
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
            case "GBP":
            case "BRL":
            case "EUR":
            case "USD":
            case "AUD":
            case "CAD":
            case "HKD":
            case "MXN":
            case "NZD":
            case "SGD":
            case "JPY":
                $formatted = $symbol . $amount . ' ' . $currency;
                break;
            default:
                $formatted = $currency . ' ' . $amount;
                break;
        }
    } else {
        switch ($currency) {
            case "GBP":
            case "BRL":
            case "EUR":
            case "USD":
            case "AUD":
            case "CAD":
            case "HKD":
            case "MXN":
            case "SGD":
            case "JPY":
                $formatted = $currency . ' ' . $amount . $symbol;
                break;
            default:
                $formatted = $amount . ' ' . $currency;
                break;
        }
    }

    return $formatted;
}

function smartpay_get_success_page_uri($query_string = null)
{
    $page_id = smartpay_get_option('payment_success_page', 0);
    $page_id = absint($page_id);

    $success_page = get_permalink($page_id);

    if ($query_string) {
        $success_page .= $query_string;
    }

    return $success_page;
}

function smartpay_insert_payment($payment_data)
{
    return Payment::insert($payment_data);
}


function smartpay_send_to_gateway($gateway, $payment_data)
{
    $payment_data['gateway_nonce'] = wp_create_nonce('smartpay-gateway');

    // $gateway must match the ID used when registering the gateway
    do_action('smartpay_' . $gateway . '_process_payment', $payment_data);

    return;
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
