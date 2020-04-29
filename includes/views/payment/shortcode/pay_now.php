<h4>Payment : <?php echo smartpay_amount_format($amount); ?></h4>

<p>Payment type : <?php echo 'recurring' == $payment_type  ? 'Subscription' : 'One Time' ?></p>
<br>

<form action="<?php echo $form_action; ?>" method="POST">

    <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>

    <input type="hidden" name="form_id" value="<?php echo $form_id ?>">
    <input type="hidden" name="amount" value="<?php echo $amount ?>">
    <input type="hidden" name="smartpay_action" value="smartpay_process_payment">

    <label for="first_name">Payment by</label>
    <?php

    $has_payment_error = false;
    $gateways = smartpay_get_enabled_payment_gateways(true);

    if (count($gateways)) :

        $chosen_gateway = isset($_REQUEST['gateway']) && smartpay_is_gateway_active($_REQUEST['gateway']) ? $_REQUEST['gateway'] : smartpay_get_default_gateway();

        foreach ($gateways as $gateway_id => $gateway) :

            $checked = checked($gateway_id, $chosen_gateway, false);

            echo '<label for="smartpay-gateway-' . esc_attr($gateway_id) . '">';
            echo '<input type="radio" name="gateway" id="smartpay-gateway-' . esc_attr($gateway_id) . '" value="' . esc_attr($gateway_id) . '"' . $checked . '>' . esc_html($gateway['checkout_label']);
            echo '</label>';

        endforeach;

    else :
        $has_payment_error = true;
        echo 'You must enable a payment gateway to proceed a payment.';
    endif;
    ?>
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

    <button type="submit" <?php if ($has_payment_error) echo 'disabled'; ?>>
        <?php echo $payment_button_text ?: 'Pay Now' ?></button>
</form>