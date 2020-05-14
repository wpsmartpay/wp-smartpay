<?php
// var_dump($product)

$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$chosen_gateway = isset($_REQUEST['gateway']) && smartpay_is_gateway_active($_REQUEST['gateway']) ? $_REQUEST['gateway'] : smartpay_get_default_gateway();
$has_payment_error = false;
?>

<div class="smartpay">
    <h3><?php echo $product->name; ?></h3>
    <p><?php echo smartpay_amount_format($product->sale_price); ?></p>
    <p><?php echo $product->description; ?></p>

    <!-- Modal content -->
    <form action="<?php echo $form_action; ?>" method="POST">

        <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>

        <input type="hidden" name="smartpay_action" value="smartpay_process_payment">
        <input type="hidden" name="smartpay_purchase_type" value="product_purchase">
        <input type="hidden" name="smartpay_product_id" value="<?php echo $product->get_ID() ?>">
        <!-- // TODO: Implement variations -->
        <ul>
            <?php if (count($gateways)) : ?>

            <?php foreach ($gateways as $gateway_id => $gateway) : ?>
            <li>
                <?php echo '<label for="smartpay-gateway-' . esc_attr($gateway_id) . '">
                        <input type="radio" name="smartpay_gateway" id="smartpay-gateway-' . esc_attr($gateway_id) . '" value="' . esc_attr($gateway_id) . '"' . checked($gateway_id, $chosen_gateway, false) . '>';
                        echo esc_html($gateway['checkout_label']);
                        echo '</label>';
                        ?>
            </li>
            <?php endforeach; ?>

            <?php else : ?>
            <?php
                $has_payment_error = true;
                echo 'You must enable a payment gateway to proceed a payment.';
                ?>
            <?php endif; ?>
        </ul>

        <input type="text" name="smartpay_first_name" value="Al-Amin">
        <input type="text" name="smartpay_last_name" value="Firdows">
        <input type="text" name="smartpay_email" value="alaminfirdows@gmail.com">
        <br>

        <button type="submit" class="smartpay product-buy" <?php if ($has_payment_error) echo 'disabled'; ?>>
            <?php echo $payment_button_text ?? 'Pay Now' ?></button>
    </form>
</div>