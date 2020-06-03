<?php

function smartpay_user_pending_verification($user_id = null)
{

    if (is_null($user_id)) {
        $user_id = get_current_user_id();
    }

    if (empty($user_id)) return;

    $pending = get_user_meta($user_id, '_smartpay_pending_verification', true);

    return (bool) apply_filters('smartpay_user_pending_verification', !empty($pending), $user_id);
}