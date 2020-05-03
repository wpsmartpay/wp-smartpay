<?php
//TODO: Reform to payment model

use ThemesGrove\SmartPay\Models\SmartPay_Payment;

$payment_id = $_SESSION['smartpay_payment_id'] ?? false;

$payment = new SmartPay_Payment($payment_id);

if ($payment_id && $payment) :
?>

<?php do_action('smartpay_before_payment_receipt', $payment); ?>

<table>

    <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

    <tr>
        <td><?php _e('Payment ID:', 'wp-smartpay') ?></td>
        <td><?php echo $payment->ID ?></td>
    </tr>
    <tr>
        <td><?php _e('Name:', 'wp-smartpay') ?></td>
        <td><?php echo $payment->first_name . ' ' . $payment->last_name ?></td>
    </tr>
    <tr>
        <td><?php _e('Email:', 'wp-smartpay') ?></td>
        <td><?php echo $payment->email ?></td>
    </tr>
    <tr>
        <td><?php _e('Payment amount:', 'wp-smartpay') ?></td>
        <td><?php echo smartpay_amount_format($payment->amount) ?></td>
    </tr>
    <tr>
        <td><?php _e('Payment gateway:', 'wp-smartpay') ?></td>
        <td><?php echo smartpay_payment_gateways()[$payment->payment_gateway]['checkout_label'] ?? ucfirst($payment->payment_gateway) ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('Payment status:', 'wp-smartpay') ?></td>
        <td><?php echo smartpay_get_payment_status($payment->ID, true) ?></td>
    </tr>

    <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

</table>

<?php do_action('smartpay_after_payment_receipt', $payment); ?>

<?php do_action('smartpay_payment_' . $payment->payment_gateway . '_receipt', $payment);
    ?>

<?php else : ?>
<p>You have no payment to show</p>
<?php

endif;