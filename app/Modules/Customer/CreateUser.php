<?php
namespace SmartPay\Modules\Customer;

use SmartPay\Models\Payment;

class CreateUser {

    public static function create_user(Payment $payment)
    {
        if (empty($username))
        {
            $username = $payment->email;
        }
        $email = sanitize_email($payment->email);
        if (!username_exists($username) && !email_exists($email))
        {
            // Handle password creation.
            $password_generated = false;
            if ( empty( $password ) ) {
                $password           = wp_generate_password();
                $password_generated = true;
            }

            $new_customer_data = array(
				'first_name' => $payment->customer->first_name ?? '',
				'last_name' => $payment->customer->last_name ?? '',
                'user_login' => $username,
                'user_pass'  => $password,
                'user_email' => $email,
                'role'       => 'Customer',
            );

            try {
                $user = wp_insert_user( $new_customer_data );
                if ($user){
                    //send notification to only new user
                    // check the new user notification
                    $enable_user_notification = (bool) smartpay_get_settings()['new_user_notification'] ?? false;
                    if ($enable_user_notification){
                        wp_new_user_notification($user, null, 'user');
                    }
                }
            }catch (\Exception $e){
                smartpay_debug_log(sprintf(__('SmartPay: User could not create, due to %s', 'smartpay'), $e->getMessage()));
            }
            return true;
        }
    }
}