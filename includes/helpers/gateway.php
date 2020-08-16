<?php

function smartpay_payment_gateways()
{
    // Default, built-in gateways
    return apply_filters('smartpay_gateways', SmartPay\Gateways\Gateway::gateways());
}

function smartpay_get_enabled_payment_gateways($sort = false)
{
    $gateways = smartpay_payment_gateways();

    $enabled  = (array) smartpay_get_option('gateways', false);

    $gateway_list = array();

    foreach ($gateways as $key => $gateway) {
        if (isset($enabled[$key]) && $enabled[$key] == 1) {
            $gateway_list[$key] = $gateway;
        }
    }

    if (true === $sort) {
        // Reorder our gateways so the default is first
        $default_gateway_id = smartpay_get_default_gateway();

        if (smartpay_is_gateway_active($default_gateway_id)) {
            $default_gateway    = array($default_gateway_id => $gateway_list[$default_gateway_id]);
            unset($gateway_list[$default_gateway_id]);

            $gateway_list = array_merge($default_gateway, $gateway_list);
        }
    }

    return $gateway_list;
}

function smartpay_is_gateway_active($gateway)
{
    $gateways = smartpay_get_enabled_payment_gateways();

    if (!is_array($gateways) || !count($gateways)) {
        return;
    }

    $is_active = array_key_exists($gateway, $gateways);
    return $is_active;
}

function smartpay_get_default_gateway()
{
    $default = smartpay_get_option('default_gateway', 'paddle');

    if (!smartpay_is_gateway_active($default)) {
        $gateways = smartpay_get_enabled_payment_gateways();
        $gateways = array_keys($gateways);
        $default  = reset($gateways);
    }

    return $default;
}