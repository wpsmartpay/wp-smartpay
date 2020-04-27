<h4>Payment : <?php echo smartpay_amount_format($amount); ?></h4>

<p>Payment type : <?php echo 'recurring' == $payment_type  ? 'Subscription' : 'One Time' ?></p>
<br>

<form action="<?php echo home_url('smartpay-checkout'); ?>" method="POST">

    <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>

    <input type="hidden" name="form_id" value="<?php echo $form_id ?>">
    <input type="hidden" name="amount" value="<?php echo $amount ?>">

    <label for="first_name">Payment by</label>
    <label for="gateway_paddle">
        <input type="radio" name="gateway" id="gateway_paddle" value="paddle">
        Paddle
    </label>
    <label for="gateway_bkash">
        <input type="radio" name="gateway" id="gateway_bkash" value="bkash">
        bKash
    </label>
    <label for="gateway_amar_pay">
        <input type="radio" name="gateway" id="gateway_amar_pay" value="amar_pay">
        Amar Pay
    </label>
    <br>

    <label for="first_name">First Name</label>
    <input type="text" name="first_name" id="first_name" placeholder="First Name" required>
    <br>

    <label for="last_name">Last Name</label>
    <input type="text" name="last_name" id="last_name" placeholder="Last Name" required>
    <br>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" placeholder="Email" required>
    <br>

    <button type="submit"> <?php echo $payment_button_text ?: 'Pay Now' ?></button>
</form>