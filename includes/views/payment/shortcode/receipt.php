<?php
//TODO: Reform to payment model

use ThemesGrove\SmartPay\Models\SmartPay_Payment;

$payment_id = $_SESSION['smartpay_payment_id'] ?? false;

$payment_data = new SmartPay_Payment($payment_id);

if ($payment_id && $payment_data) :
?>

<?php do_action('smartpay_before_payment_receipt', $payment_data); ?>

<table>

    <?php do_action('smartpay_before_payment_receipt_data', $payment_data); ?>

    <tr>
        <td><?php _e('Name:', 'wp-smartpay') ?></td>
        <td><?php echo ($payment_data->first_name . ' ' . $payment_data->last_name) ?></td>
    </tr>
    <tr>
        <td><?php _e('Email:', 'wp-smartpay') ?></td>
        <td><?php echo ($payment_data->email) ?></td>
    </tr>
    <tr>
        <td><?php _e('Payment amount:', 'wp-smartpay') ?></td>
        <td><?php echo (smartpay_amount_format($payment_data->amount)) ?></td>
    </tr>
    <tr>
        <td><?php _e('Payment gateway:', 'wp-smartpay') ?></td>
        <td><?php echo (smartpay_payment_gateways()[$payment_data->gateway]['checkout_label'] ?? ucfirst($payment_data->gateway)) ?>
        </td>
    </tr>

    <?php do_action('smartpay_before_payment_receipt_data', $payment_data); ?>

</table>

<?php do_action('smartpay_after_payment_receipt', $payment_data); ?>

<?php else : ?>
<p>You have no payment to show</p>
<?php

endif;