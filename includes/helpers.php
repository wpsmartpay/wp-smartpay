<?php

use SmartPay\Models\SmartPay_Payment;
use SmartPay\Payment;
use SmartPay\Setting;

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
    return Setting::get_settings();
}

function smartpay_get_option($key = '', $default = false)
{
    global $smartpay_options;
    $value = !empty($smartpay_options[$key]) ? $smartpay_options[$key] : $default;
    return $value;
}

function smartpay_payment_gateways()
{
    // Default, built-in gateways
    $gateways = array(
        'paddle' => array(
            'admin_label'    => __('Paddle', 'smartpay'),
            'checkout_label' => __('Paddle', 'smartpay'),
        ),
    );

    return apply_filters('smartpay_payment_gateways', $gateways);
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

function smartpay_get_payment($payment_or_txn_id, $by_txn = false)
{
    if (!$payment_or_txn_id) {
        return;
    }

    return new SmartPay_Payment($payment_or_txn_id, $by_txn = false);
}

/**
 * Sets a Transaction ID in post meta for the given Payment ID
 *
 * @since  2.1
 * @param int $payment_id Payment ID
 * @param string $transaction_id The transaction ID from the gateway
 * @return mixed Meta ID if successful, false if unsuccessful
 */
function smartpay_set_payment_transaction_id($payment_id = 0, $transaction_id = '')
{
    if (empty($payment_id) || empty($transaction_id)) {
        return false;
    }

    $transaction_id = apply_filters('smartpay_set_payment_transaction_id', $transaction_id, $payment_id);

    return smartpay_update_payment_meta($payment_id, '_smartpay_payment_transaction_id', $transaction_id);
}

/**
 * Update the meta for a payment
 * @param  integer $payment_id Payment ID
 * @param  string  $meta_key   Meta key to update
 * @param  string  $meta_value Value to update to
 * @param  string  $prev_value Previous value
 * @return mixed   Meta ID if successful, false if unsuccessful
 */
function smartpay_update_payment_meta($payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '')
{
    $payment = new SmartPay_Payment($payment_id);
    return $payment->update_meta($meta_key, $meta_value, $prev_value);
}


/**
 * Get Payment Status
 *
 * @since 1.0
 *
 * @param int  Payment ID
 * @param bool   $return_label Whether to return the payment status or not
 *
 * @return bool|mixed if payment status exists, false otherwise
 */
function smartpay_get_payment_status($payment_id, $return_label = false)
{
    $payment = smartpay_get_payment($payment_id);

    if (!is_object($payment)) {
        return false;
    }

    if (true === $return_label) {
        $status = smartpay_get_payment_status_label($payment->status);
    } else {
        $keys      = smartpay_get_payment_status_keys();
        $found_key = array_search(strtolower($payment->status), $keys);
        $status    = array_key_exists($found_key, $keys) ? $keys[$found_key] : false;
    }

    return !empty($status) ? $status : false;
}

/**
 * Retrieves keys for all available statuses for payments
 *
 * @since 2.3
 * @return array $payment_status All the available payment statuses
 */
function smartpay_get_payment_status_keys()
{
    $statuses = array_keys(smartpay_get_payment_statuses());
    asort($statuses);

    return array_values($statuses);
}

/**
 * Given a payment status string, return the label for that string.
 *
 * @since 2.9.2
 * @param string $status
 *
 * @return bool|mixed
 */
function smartpay_get_payment_status_label($status = '')
{
    $statuses = smartpay_get_payment_statuses();

    if (!is_array($statuses) || empty($statuses)) {
        return false;
    }

    if (array_key_exists($status, $statuses)) {
        return $statuses[$status];
    }

    return false;
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

function smartpay_is_test_mode()
{
    $is_test_mode = smartpay_get_option('test_mode', false);
    return (bool)  $is_test_mode;
}

function smartpay_get_purchase_session()
{
    // TODO: Change to payment key
    return $_SESSION['smartpay_payment_id'] ?? false;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since 1.0.8.1
 * @return array $payment_status All the available payment statuses
 */
function smartpay_get_payment_statuses()
{
    $payment_statuses = array(
        'pending'   => __('Pending', 'smartpay'),
        'publish'   => __('Complete', 'smartpay'),
        'refunded'  => __('Refunded', 'smartpay'),
        'failed'    => __('Failed', 'smartpay'),
        'abandoned' => __('Abandoned', 'smartpay'),
        'revoked'   => __('Revoked', 'smartpay'),
        'processing' => __('Processing', 'smartpay')
    );

    return apply_filters('smartpay_payment_statuses', $payment_statuses);
}
