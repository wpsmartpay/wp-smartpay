<?php $customer_name = $payment->customer['first_name'] . ' ' . $payment->customer['last_name']; ?>

<p><?php echo __('Dear', 'smartpay') . ' ' . $customer_name . ','; ?></p>
<?php echo __('Paid amount: ', 'smartpay') . ' ' . $payment->amount; ?>
<p><?php _e('Thank you for your payment.', 'smartpay'); ?></p>