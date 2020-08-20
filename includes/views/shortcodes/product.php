<?php
$product_price = $product->sale_price > -1 ? $product->sale_price : $product->base_price;

$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$_gateway = \sanitize_text_field($_REQUEST['gateway'] ?? '');

$chosen_gateway = isset($_gateway) && smartpay_is_gateway_active($_gateway) ? $_gateway : smartpay_get_default_gateway();
$has_payment_error = false;
// var_dump($behavior);
// exit;
?>

<?php if ('embedded' == $behavior) : ?>
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
                        <div class="col-sm-12 col-md-7 mb-3">
                            <?php if ($product->title) : ?>
                                <h2 class="card-title product--title mt-0 mb-2"><?php echo $product->title; ?></h2>
                            <?php endif; ?>

                            <?php if ($product->description) : ?>
                                <div class="card-text product--description">
                                    <?php echo $product->description; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-12 col-md-5">
                            <div class="product--price-section">
                                <div class="product-variations mb-2">
                                    <ul class="list-group m-0">
                                        <?php if ($product->has_variations()) : ?>
                                            <!-- // Variation prices -->
                                            <?php foreach ($product->variations as $index => $variation) : ?>
                                                <li class="list-group-item variation price m-0 mb-3 py-4 <?php echo 0 == $index ? 'selected' : ''; ?>">
                                                    <label for="smartpay_product_variation_id_<?php echo esc_attr($variation['id']); ?>" class="d-block m-0">
                                                        <input class="d-none" type="radio" name="smartpay_product_variation_id" id="smartpay_product_variation_id_<?php echo esc_attr($variation['id']); ?>" value="<?php echo esc_attr($variation['id']); ?>" <?php echo 0 == $index ? 'checked' : ''; ?>>
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
                                                <label class="d-block m-0">
                                                    <span class="price--amount"><?php echo smartpay_amount_format($product_price); ?></span>
                                                    <h5 class="m-0 mt-3 price--title">Item Price</h5>
                                                </label>
                                            </li>
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

            <!-- Form Data -->
            <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="product_purchase">
            <input type="hidden" name="smartpay_product_id" id="smartpay_product_id" value="<?php echo $product->ID ?? 0; ?>">
            <!-- /Form Data -->

            <!-- Payment modal -->
            <?php include "shared/payment-modal.php" ?>
        </div>
    </div>

<?php else : ?>
    <div class="smartpay" style="background: transparent;">
        <div class="smartpay-product-shortcode">
            <div class="modal fade product-modal">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl py-5">
                    <div class="modal-content align-content-between" style="background: transparent; border: 0;">
                        <div class="d-flex justify-content-end">
                            <div class="modal-button py-3">
                                <button type="button" class="btn modal-close close" data-dismiss="modal"><i data-feather="x" width="21" height="21"></i></button>
                            </div>
                        </div>
                        <div class="modal-body text-center mb-4">
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
                                                <div class="col-sm-12 col-md-7 mb-3">
                                                    <?php if ($product->title) : ?>
                                                        <h2 class="card-title product--title mt-0 mb-2"><?php echo $product->title; ?></h2>
                                                    <?php endif; ?>

                                                    <?php if ($product->description) : ?>
                                                        <div class="card-text product--description">
                                                            <?php echo $product->description; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-sm-12 col-md-5">
                                                    <div class="product--price-section">
                                                        <div class="product-variations mb-2">
                                                            <ul class="list-group m-0">
                                                                <?php if ($product->has_variations()) : ?>
                                                                    <!-- // Variation prices -->
                                                                    <?php foreach ($product->variations as $index => $variation) : ?>
                                                                        <li class="list-group-item variation price m-0 mb-3 py-4 <?php echo 0 == $index ? 'selected' : ''; ?>">
                                                                            <label for="smartpay_product_variation_id_<?php echo esc_attr($variation['id']); ?>" class="d-block m-0">
                                                                                <input class="d-none" type="radio" name="smartpay_product_variation_id" id="smartpay_product_variation_id_<?php echo esc_attr($variation['id']); ?>" value="<?php echo esc_attr($variation['id']); ?>" <?php echo 0 == $index ? 'checked' : ''; ?>>
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
                                                                        <label class="d-block m-0">
                                                                            <span class="price--amount"><?php echo smartpay_amount_format($product_price); ?></span>
                                                                            <h5 class="m-0 mt-3 price--title">Item Price</h5>
                                                                        </label>
                                                                    </li>
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

                                    <!-- Form Data -->
                                    <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="product_purchase">
                                    <input type="hidden" name="smartpay_product_id" id="smartpay_product_id" value="<?php echo $product->ID ?? 0; ?>">
                                    <!-- /Form Data -->

                                    <!-- Payment modal -->
                                    <?php include "shared/payment-modal.php" ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-success open-product-modal">
                <?php echo _e($label ?: 'Buy now', 'smartpay'); ?>
            </button>
        </div>
    </div>
<?php endif; ?>