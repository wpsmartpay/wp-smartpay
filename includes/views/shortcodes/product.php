<?php
$product_price = $product->sale_price > -1 ? $product->sale_price : $product->base_price;
$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$chosen_gateway = isset($_REQUEST['gateway']) && smartpay_is_gateway_active($_REQUEST['gateway']) ? $_REQUEST['gateway'] : smartpay_get_default_gateway();
$has_payment_error = false;
?>

<div class="smartpay">
    <div class="smartpay-product-shortcode smartpay-payment">
        <!-- Product details -->
        <div class="card product">
            <?php if ($product->image) : ?>
            <div class="bg-light product--image border-bottom">
                <img src="<?php echo $product->image; ?>" class="card-img-top">
            </div>
            <?php endif; ?>

            <div class="card-body p-5">
                <div class="row">
                    <div class="col-7">
                        <?php if ($product->title) : ?>
                        <h2 class="card-title product--title mt-0 mb-2"><?php echo $product->title; ?></h2>
                        <?php endif; ?>

                        <?php if ($product->description) : ?>
                        <div class="card-text product--description">
                            <?php echo $product->description; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-5">
                        <div class="product--price-section">
                            <div class="product-variations mb-2">
                                <ul class="list-group m-0">
                                    <?php if ($product->has_variations()) : ?>
                                    <!-- // Variation prices -->
                                    <?php foreach ($product->variations as $index => $variation) : ?>
                                    <li class="list-group-item variation price m-0 mb-3 py-4 <?php echo 0 == $index ? 'selected' : ''; ?>">
                                        <label for="smartpay_product_variation_id_<?php echo esc_attr($variation['id']); ?>" class="d-block m-0">
                                            <input class="d-none" type="radio" name="smartpay_product_variation_id" id="smartpay_product_variation_id_<?php echo esc_attr($variation['id']); ?>" value="<?php echo esc_attr($variation['id']); ?>" checked>
                                            <!-- // TODO: Add price to Product Variation -->
                                            <span class="price--amount"><?php echo smartpay_amount_format(($product_price + $variation['additional_amount'])); ?></span>
                                            <strong class="price--title"><?php echo esc_html(ucfirst($variation['name'])); ?></strong>
                                            <?php if ($variation['description'] ?? false) : ?>
                                            <p class="variation--description m-0">
                                                <?php echo esc_attr($variation['description']); ?>
                                            </p>
                                            <?php endif; ?>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>

                                    <!-- Product price -->
                                    <?php else : ?>
                                    <li class="list-group-item price m-0 my-2 py-4 selected">
                                        <label for="product_price" class="d-block m-0">
                                            <input class="d-none" type="radio" name="smartpay_product_variation_id" id="product_price" checked>
                                            <span class="price--amount"><?php echo smartpay_amount_format($product_price); ?></span>
                                            <h5 class="m-0 mt-3 price--title">Item Price</h5>
                                        </label>
                                    </li>
                                    </p>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-success btn-block btn-lg open-payment-form">
                                <?php echo _e('Pay', 'smartpay'); ?>
                            </button>
                        </div>
                    </div> <!-- col -->
                </div> <!-- row -->
            </div> <!-- card-body -->
        </div>

        <!-- Payment modal -->
        <div class="modal fade payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content align-content-between">
                    <div class="text-center p-4 pb-5">
                        <div class="mt-2">
                            <h4 class="payment-modal--small-title m-0 mb-3">
                                <?php echo __('Payment For', 'smartpay'); ?>
                            </h4>
                            <h2 class="payment-modal--title m-0 mb-3">
                                <?php echo $product->title ?? 'Product/Form'; ?>
                            </h2>
                            <p class="payment-modal--subtitle m-0 mb-3"><?php _e('Enter your info and complete payment!', 'smartpay'); ?></p>
                        </div>
                    </div>

                    <div class="modal-body text-center mb-4 step-1
                        <div class=" col-9 col-lg-6 text-center pb-4">
                        <form action="<?php echo $form_action; ?>" method="POST">
                            <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>
                            <input type="hidden" name="smartpay_action" value="smartpay_process_payment">
                            <input type="hidden" name="smartpay_purchase_type" value="product_purchase">
                            <input type="hidden" name="smartpay_product_id" value="<?php echo $product->get_ID() ?>">
                            <!-- // FIXME: Add variation id -->

                            <div class="payment-modal--gateway">

                                <!-- // If only one gateway activated -->
                                <?php if (count($gateways) == 1) : ?>
                                <p class="payment-gateway--label single-gateway"><?php echo sprintf(__('Pay with ', 'smartpay') . ' <strong>%s</strong>', esc_html(reset($gateways)['checkout_label'])); ?></p>

                                <!-- // If it has multiple payment gateway -->
                                <?php elseif (count($gateways) > 1) : ?>
                                <p class="payment-gateway--label"><?php echo _e('Select a gateway', 'smartpay'); ?></p>

                                <ul class="gateways list-unstyled list-group-horizontal-sm list-group m-0 my-3 justify-content-center">
                                    <?php foreach ($gateways as $gateway_id => $gateway) : ?>
                                    <li class="gateway list-group-item p-0 m-0 px-3 py-1">
                                        <label for="<?php echo 'smartpay_gateway_' . esc_attr($gateway_id); ?>" class="gateway--label">
                                            <input type="radio" class="mr-2" name="smartpay_gateway" id="<?php echo 'smartpay_gateway_' . esc_attr($gateway_id); ?>" value="<?php esc_attr($gateway_id) ?>" <?php echo checked($gateway_id, $chosen_gateway, false); ?>>
                                            <?php echo esc_html($gateway['checkout_label']); ?>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else : ?>

                                <?php
                                    $has_payment_error = true;
                                    echo 'You must enable a payment gateway to proceed a payment.';
                                    ?>
                                <?php endif; ?>
                            </div>

                            <!-- <div class="payment-modal--amount pb-1 d-none">
                                    <p class="payment-gateway--label single-gateway"><?php //echo sprintf(__('Total: ', 'smartpay') . ' <strong id="show_amount">$</strong>'); ?></p>
                                </div> -->

                            <div class="payment-modal--user-info mt-3">
                                <div class="row">
                                    <div class="col-sm-6 form-group mb-3">
                                        <input type="text" placeholder="First name" class="form-control" name="smartpay_first_name" id="smartpay_first_name" autocomplete="first_name" required>
                                    </div>
                                    <div class="col-sm-6 form-group mb-3">
                                        <input type="text" placeholder="Last name" class="form-control" name="smartpay_last_name" id="smartpay_last_name" autocomplete="last_name">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group mb-3">
                                        <input type="email" placeholder="Email address" class="form-control" name="smartpay_email" id="smartpay_email" autocomplete="email" required>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-success btn-block btn-lg smartpay-pay-now" <?php if ($has_payment_error) echo 'disabled'; ?>>
                                    <?php echo _e('Pay Now', 'smartpay'); ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="modal-body text-center mb-4 step-2">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>