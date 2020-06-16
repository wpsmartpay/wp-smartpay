<?php $customer_name = $payment->customer['first_name'] . ' ' . $payment->customer['last_name']; ?>

<p><?php echo __('Dear', 'smartpay') . ' ' . $customer_name . ','; ?></p>
<p><?php _e('Thank you for your payment.', 'smartpay'); ?></p>