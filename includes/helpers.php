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

function smartpay_gateways()
{
    return Gateway::gateways();
}