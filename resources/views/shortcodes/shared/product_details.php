<div class="smartpay" style="margin: 0 auto;">
    <div class="smartpay-product-shortcode smartpay-payment">
        <!-- Product details -->
        <div class="card product">
            <?php if (count($product->covers)) : ?>
            <div class="bg-light product--image border-bottom">
                <img src="<?php echo $product->covers[0]['url']; ?>" class="card-img-top">
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
                            <?php echo wpautop($product->description); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-12 col-md-5">
                        <div class="product--price-section">
                            <div class="product-variations mb-2">
                                <ul class="list-group">
                                    <?php
                                    if (count($product->variations)) : ?>
                                    <!-- Variations -->
                                    <?php foreach ($product->variations as $index => $variation) : ?>
                                    <?php $billingType = $variation->extra['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>
                                    <?php $billingPeriod = $variation->extra['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY; ?>
                                    <li class="list-group-item variation price <?php echo 0 == $index ? 'selected' : ''; ?>">
                                        <label for="<?php echo "product_variation_{$variation->id}"; ?>" class="d-block m-0">
                                            <input type="hidden" name="_smartpay_product_id" id="<?php echo "product_variation_{$variation->id}"; ?>" value="<?php echo esc_attr($variation->id); ?>" <?php echo 0 == $index ? 'checked' : ''; ?>>
                                            <input type="hidden" name="_product_billing_type" id="_product_billing_type_<?php echo $variation->id; ?>" value="<?php echo $billingType; ?>">
                                            <?php if (\SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $billingType) : ?>
                                            <input type="hidden" name="_product_billing_period" id="_product_billing_period_<?php echo $variation->id; ?>" value="<?php echo $billingPeriod; ?>">
                                            <?php endif; ?>
                                            <div class="price--amount">
                                                <span class="sale-price"><?php echo smartpay_amount_format(($variation->price)); ?></span>
                                                <?php if ($variation->sale_price && ($variation->base_price > $variation->sale_price)) : ?>
                                                <del class="base-price"><?php echo smartpay_amount_format($variation->base_price); ?></del>
                                                <?php endif; ?>
                                                <?php if (\SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $billingType) : ?>
                                                <span>/ <?php echo $billingPeriod; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="m-0 price--title">
                                                <?php echo esc_html($variation->title); ?>
                                            </h5>
                                            <?php if ($variation->description ?? false) : ?>
                                            <p class="variation--description m-0">
                                                <?php echo wpautop($variation->description); ?>
                                            </p>
                                            <?php endif; ?>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>

                                    <!-- Product price -->
                                    <?php else : ?>
                                    <?php $productBillingType = $product->extra['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>
                                    <?php $productBillingPeriod = $product->extra['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY; ?>
                                    <li class="list-group-item price selected">
                                        <label class="d-block m-0">
                                            <div class="price--amount">
                                                <span class="sale-price"><?php echo smartpay_amount_format($product->sale_price); ?></span>
                                                <?php if ($product->sale_price && ($product->base_price > $product->sale_price)) : ?>
                                                <del class="base-price"><?php echo smartpay_amount_format($product->base_price); ?></del>
                                                <?php endif; ?>
                                                <?php if (\SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $productBillingType) : ?>
                                                <span>/ <?php echo $productBillingPeriod; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="m-0 price--title">
                                                <?php echo _e('Product Price', 'smartpay'); ?>
                                            </h5>
                                        </label>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-success btn-block btn-lg open-payment-form">
                                <?php echo _e('Get it now', 'smartpay'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if (count($product->variations)) {
            $defaultVariation = $product->variations->first();
        } else {
            $defaultVariation = $product;
        }
        ?>
        <!-- Form Data -->
        <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="product_purchase">
        <input type="hidden" name="smartpay_product_id" value="<?php echo esc_attr($defaultVariation->id); ?>">
        <input type="hidden" name="smartpay_product_price" value="<?php echo smartpay_amount_format($defaultVariation->sale_price ?? 0); ?>">
        <input type="hidden" name="smartpay_product_billing_type" value="<?php echo $defaultVariation->extra['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>">
        <input type="hidden" name="smartpay_product_billing_period" value="<?php echo $defaultVariation->extra['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY; ?>">
        <!-- /Form Data -->

        <!-- Payment modal -->
        <?php include  __DIR__ . '/payment_modal.php'; ?>
    </div>
</div>