<?php

// TODO: Update doc block

use SmartPay\Payments\SmartPay_Payment;

function smartpay_set_session_payment_data($payment_data)
{
    return SmartPay()->session->set_payment_data($payment_data);
}

function smartpay_get_session_payment_data()
{
    return SmartPay()->session->get_payment_data();
}

function smartpay_set_session_payment_id($payment_id)
{
    return SmartPay()->session->set_payment_id($payment_id);
}

function smartpay_get_session_payment_id()
{
    return SmartPay()->session->get_payment_id();
}

function smartpay_get_payment_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_get_payment_success_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_success_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_get_payment_failure_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_failure_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_insert_payment($payment_data)
{
    return SmartPay()->payment->insert_payment($payment_data);
}

function smartpay_get_payment($payment_or_txn_id, $by_txn = false)
{
    if (!$payment_or_txn_id) {
        return;
    }

    return SmartPay()->payment->get_payment($payment_or_txn_id, $by_txn);
}

function smartpay_set_payment_transaction_id($payment_id = 0, $transaction_id = '')
{
    if (empty($payment_id) || empty($transaction_id)) {
        return false;
    }

    $transaction_id = apply_filters('smartpay_set_payment_transaction_id', $transaction_id, $payment_id);

    // TODO: Reform it
    return smartpay_update_payment_meta($payment_id, '_smartpay_payment_transaction_id', $transaction_id);
}

function smartpay_update_payment_meta($payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '')
{
    // TODO: Reform it
    $payment = new SmartPay_Payment($payment_id);
    return $payment->update_meta($meta_key, $meta_value, $prev_value);
}

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
 * @since 0.0.1
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
 * @since 0.0.1
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
