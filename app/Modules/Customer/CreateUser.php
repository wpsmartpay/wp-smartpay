<?php
namespace SmartPay\Modules\Customer;

use SmartPay\Models\Payment;

class CreateUser {

    public function create_user(Payment $payment)
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
                'user_login' => $username,
                'user_pass'  => $password,
                'user_email' => $email,
                'role'       => 'Customer',
            );

            try {
                $user = wp_insert_user( $new_customer_data );
                if ($user){
                    //send notification
                    wp_new_user_notification($user, null, 'both');
                }
            }catch (\Exception $e){
                smartpay_debug_log(sprintf(__('SmartPay: User could not create, due to %s', 'smartpay'), $e->getMessage()));
            }
            return true;
        }
    }
}