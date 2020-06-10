<?php
if ($payment) :
?>

    <?php do_action('smartpay_before_payment_receipt', $payment); ?>

    <table>

        <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

        <tr>
            <td><?php _e('Payment ID:', 'smartpay') ?></td>
            <td><?php echo $payment->ID ?></td>
        </tr>
        <tr>
            <td><?php _e('Name:', 'smartpay') ?></td>
            <td><?php echo $payment->customer->first_name . ' ' . $payment->customer->last_name; ?></td>
        </tr>
        <tr>
            <td><?php _e('Email:', 'smartpay') ?></td>
            <td><?php echo $payment->email ?></td>
        </tr>
        <tr>
            <td><?php _e('Payment amount:', 'smartpay') ?></td>
            <td><?php echo smartpay_amount_format($payment->amount) ?></td>
        </tr>
        <tr>
            <td><?php _e('Payment gateway:', 'smartpay') ?></td>
            <td><?php echo smartpay_payment_gateways()[$payment->gateway]['checkout_label'] ?? ucfirst($payment->gateway) ?>
            </td>
        </tr>
        <tr>
            <td><?php _e('Payment status:', 'smartpay') ?></td>
            <td><?php echo smartpay_get_payment_status($payment->ID, true) ?></td>
        </tr>

        <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

    </table>

    <?php do_action('smartpay_after_payment_receipt', $payment); ?>

    <?php //do_action('smartpay_payment_' . $payment->gateway . '_receipt', $payment);
    ?>

<?php else : ?>
    <p>You have no payment to show</p>
<?php

endif;
