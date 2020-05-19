<?php
// var_dump($product);
$product_price = $product->sale_price > -1 ? $product->sale_price : $product->base_price;
$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$chosen_gateway = isset($_REQUEST['gateway']) && smartpay_is_gateway_active($_REQUEST['gateway']) ? $_REQUEST['gateway'] : smartpay_get_default_gateway();
$has_payment_error = false;
?>

<div class="smartpay">
    <div class="card">
        <form id="payment_form" action="<?php echo $form_action; ?>" method="POST">
            <div class="bg-light border-bottom">
                <img src="<?php echo $product->image; ?>" class="card-img-top">
            </div>
            <div class="card-body p-5">
                <div class="row">
                    <div class="col-8">
                        <h2 class="card-title mt-0 mb-2"><?php echo $product->title; ?></h2>
                        <div class="card-text"><?php echo $product->description; ?></div>

                        <?php if ($product->has_variations()) : ?>
                        <div class="product-variations">
                            <ul>
                                <?php foreach ($product->variations as $variation) : ?>
                                <li>
                                    <?php echo '<label for="smartpay-product-variation-' . esc_attr($variation['id']) . '">
										<input type="radio" name="smartpay_product_variation_id" id="smartpay-product-variation-' . esc_attr($variation['id']) . '" value="' . esc_attr($variation['id']) . '" checked>';
                                            echo esc_html($variation['name']) . ' - ' . smartpay_amount_format(($product_price + $variation['additional_amount']));
                                            echo '</label>';
                                            ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php else : ?>
                        <p class="price"><?php echo smartpay_amount_format($product_price); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="col">

                        <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>

                        <input type="hidden" name="smartpay_action" value="smartpay_process_payment">
                        <input type="hidden" name="smartpay_purchase_type" value="product_purchase">
                        <input type="hidden" name="smartpay_product_id" value="<?php echo $product->get_ID() ?>">
                        <ul class="list-unstyled">
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

                        <button id="pay_now" type="button" class="btn btn-primary btn-block btn-lg"
                            <?php if ($has_payment_error) echo 'disabled'; ?>>
                            <?php echo $payment_button_text ?? 'Pay Now' ?></button>
                    </div> <!-- col -->
                </div> <!-- row -->
            </div> <!-- card-body -->
        </form>
    </div> <!-- card -->

    <!-- Modal -->
    <div class="modal fade" id="smartpay_payment_gateway_popup" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <!-- .smartpay -->