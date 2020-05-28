<?php

//edd_customer_delete

function smartpay_user_pending_verification($user_id = null)
{

    if (is_null($user_id)) {
        $user_id = get_current_user_id();
    }

    // No need to run a DB lookup on an empty user id
    if (empty($user_id)) {
        return false;
    }

    $pending = get_user_meta($user_id, '_edd_pending_verification', true);

    return (bool) apply_filters('edd_user_pending_verification', !empty($pending), $user_id);
}
