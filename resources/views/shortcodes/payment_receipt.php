<?php
$data = [
    'user_id' => '1',
    'payment_id' => $_POST['razorpay_payment_id'],
    'amount' => $_POST['totalAmount'],
    'product_id' => $_POST['product_id'],
];

if ($data['payment_id']) {
    $pay = \SmartPay\Models\Payment::find($payment->id);
    $pay->updateStatus(\SmartPay\Models\Payment::COMPLETED);
}
print_r($data);
// you can write your database insertation code here
// after successfully insert transaction in database, pass the response accordingly
$arr = array('msg' => 'Payment successfully credited', 'status' => true);
echo json_encode($arr);
?>



<?php if ($payment) : ?>

    <?php do_action('smartpay_before_payment_receipt', $payment); ?>

    <table>
        <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

        <tr>
            <td><?php _e('Payment ID:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->id); ?></td>
        </tr>
        <tr>
            <td><?php _e('Name:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->customer->first_name . ' ' . $payment->customer->last_name); ?></td>
        </tr>
        <tr>
            <td><?php _e('Email:', 'smartpay') ?></td>
            <td><?php echo esc_html($payment->email); ?></td>
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
            <td><?php echo esc_html(ucfirst($payment->status)); ?></td>
        </tr>

        <?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

    </table>

    <?php do_action('smartpay_after_payment_receipt', $payment); ?>

    <?php do_action('smartpay_payment_' . $payment->gateway . '_receipt', $payment); ?>

<?php

endif;
