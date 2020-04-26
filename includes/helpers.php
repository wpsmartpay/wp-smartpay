<?php

use ThemesGrove\SmartPay\Gateway;
use ThemesGrove\SmartPay\Setting;

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

function smartpay_get_currencies()
{
    // TODO:: Update currency text
    return array(
        'USD' => esc_html__('USD'),
        'EUR' => esc_html__('EUR'),
        'GBP' => esc_html__('GBP'),
        'ARS' => esc_html__('ARS'),
        'AUD' => esc_html__('AUD'),
        'BRL' => esc_html__('BRL'),
        'CAD' => esc_html__('CAD'),
        'CHF' => esc_html__('CHF'),
        'CNY' => esc_html__('CNY'),
        'CZK' => esc_html__('CZK'),
        'DKK' => esc_html__('DKK'),
        'HKD' => esc_html__('HKD'),
        'HUF' => esc_html__('HUF'),
        'INR' => esc_html__('INR'),
        'JPY' => esc_html__('JPY'),
        'KRW' => esc_html__('KRW'),
        'MXN' => esc_html__('MXN'),
        'NZD' => esc_html__('NZD'),
        'PLN' => esc_html__('PLN'),
        'RUB' => esc_html__('RUB'),
        'SEK' => esc_html__('SEK'),
        'SGD' => esc_html__('SGD'),
        'TWD' => esc_html__('TWD'),
        'ZAR' => esc_html__('ZAR')
    );
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
