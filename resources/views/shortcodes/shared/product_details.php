<?php defined('ABSPATH') || exit; ?>
<div class="smartpay" style="margin: 0 auto;">
    <div class="smartpay-product-shortcode smartpay-payment">
        <!-- Product details -->
        <div class="card product">
            <?php
            $smartpay_product = $smartpay_view_data['product'] ?? null;
            if (count($smartpay_product->covers)) : ?>
                <div class="bg-light product--image border-bottom">
                    <img src="<?php echo esc_url($smartpay_product->covers[0]['url']); ?>" class="card-img-top">
                </div>
            <?php endif; ?>

            <div class="card-body p-5">
                <div class="row">
                    <div class="col-sm-12 col-md-7 mb-3">
                        <?php if ($smartpay_product->title) : ?>
                            <h2 class="card-title product--title mt-0 mb-2"><?php echo esc_html($smartpay_product->title); ?></h2>
                        <?php endif; ?>

                        <?php if ($smartpay_product->description) : ?>
                            <div class="card-text product--description">
                                <?php echo wp_kses_post(wpautop($smartpay_product->description)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-12 col-md-5">
                        <div class="product--price-section">
                            <div class="product-variations mb-2">
                                <ul class="list-group">
                                    <?php
                                    if (count($smartpay_product->variations)) : ?>
                                        <!-- Variations -->
                                        <?php foreach ($smartpay_product->variations as $smartpay_index => $smartpay_variation) : ?>

                                            <?php $smartpay_billing_type = $smartpay_variation->extra['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>
                                            <?php $smartpay_billing_period = $smartpay_variation->extra['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY; ?>
                                            <li class="list-group-item variation price <?php echo 0 == $smartpay_index ? 'selected' : ''; ?>">
                                                <input type="hidden" name="_product_additional_charge"
                                                       value="<?php echo esc_attr($smartpay_variation->extra['additional_charge'] ?? 0) ?>">
                                                <label for="<?php echo esc_attr("product_variation_{$smartpay_variation->id}"); ?>" class="d-block m-0">
                                                    <input type="hidden" name="_smartpay_product_id" id="<?php echo esc_attr("product_variation_{$smartpay_variation->id}"); ?>" value="<?php echo esc_attr($smartpay_variation->id); ?>" <?php echo 0 == $smartpay_index ? 'checked' : ''; ?>>
                                                    <input type="hidden" name="_product_billing_type" id="_product_billing_type_<?php echo esc_attr($smartpay_variation->id); ?>" value="<?php echo esc_attr($smartpay_billing_type); ?>">
                                                    <?php if (\SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $smartpay_billing_type) : ?>
                                                        <input type="hidden" name="_product_billing_period" id="_product_billing_period_<?php echo esc_attr($smartpay_variation->id); ?>" value="<?php echo esc_attr($smartpay_billing_period); ?>">
                                                    <?php endif; ?>
                                                    <div class="price--amount">
                                                        <span class="sale-price" data-price="<?php echo esc_attr($smartpay_variation->sale_price); ?>"><?php echo
                                                            esc_html(smartpay_amount_format(($smartpay_variation->price))); ?></span>
                                                        <?php if ($smartpay_variation->sale_price && ($smartpay_variation->base_price > $smartpay_variation->sale_price)) : ?>
                                                            <del class="base-price"><?php echo esc_html(smartpay_amount_format($smartpay_variation->base_price)); ?></del>
                                                        <?php endif; ?>
                                                        <?php if (\SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $smartpay_billing_type) : ?>
                                                            <span>/ <?php echo esc_html($smartpay_billing_period); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h5 class="m-0 price--title">
                                                        <?php echo esc_html($smartpay_variation->title); ?>
                                                    </h5>
                                                    <?php if ($smartpay_variation->description ?? false) : ?>
                                                        <p class="variation--description m-0">
                                                            <?php echo wp_kses_post(wpautop($smartpay_variation->description)); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>

                                        <!-- Product price -->
                                    <?php else : ?>
                                        <?php $smartpay_product_billing_type = $smartpay_product->extra['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>
                                        <?php $smartpay_product_billing_period = $smartpay_product->extra['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY; ?>
                                        <li class="list-group-item price selected">
                                            <label class="d-block m-0">
                                                <div class="price--amount">
                                                    <?php if ($smartpay_product->sale_price <= 0) : ?>
                                                        <span class="sale-price"><?php echo esc_html__('Free', 'smartpay'); ?></span>
                                                        <?php if ($smartpay_product->base_price > $smartpay_product->sale_price) : ?>
                                                            <del class="base-price"><?php echo esc_html(smartpay_amount_format($smartpay_product->base_price)); ?></del>
                                                        <?php endif; ?>
                                                    <?php else : ?>
                                                        <span class="sale-price"><?php echo esc_html(smartpay_amount_format($smartpay_product->sale_price)); ?></span>
                                                        <?php if ($smartpay_product->sale_price && ($smartpay_product->base_price > $smartpay_product->sale_price)) : ?>
                                                            <del class="base-price"><?php echo esc_html(smartpay_amount_format($smartpay_product->base_price)); ?></del>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (\SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $smartpay_product_billing_type) : ?>
                                                        <span>/ <?php echo esc_html($smartpay_product_billing_period); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <h5 class="m-0 price--title">
                                                    <?php echo esc_html__('Product Price', 'smartpay'); ?>
                                                </h5>
                                            </label>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-success btn-block btn-lg open-payment-form">
                                <?php echo esc_html(($smartpay_product['settings']['payButtonLabel'] ?? '') ?: __('Get it now', 'smartpay')); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if (count($smartpay_product->variations)) {
            $smartpay_default_variation = $smartpay_product->variations->first();
        } else {
            $smartpay_default_variation = $smartpay_product;
        }
        ?>
        <!-- Form Data -->
        <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="product_purchase">
        <input type="hidden" name="smartpay_product_id" value="<?php echo esc_attr($smartpay_default_variation->id); ?>">
        <input type="hidden" name="smartpay_product_price" value="<?php echo esc_attr($smartpay_default_variation->sale_price ?? 0); ?>">
        <input type="hidden" name="smartpay_product_billing_type" value="<?php echo esc_attr($smartpay_default_variation->extra['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME); ?>">
        <input type="hidden" name="smartpay_product_billing_period" value="<?php echo esc_attr($smartpay_default_variation->extra['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY); ?>">

        <input type="hidden" name="smartpay_product_additional_charge" value="<?php echo esc_attr($smartpay_default_variation->extra['additional_charge'] ?? 0); ?>">
        <input type="hidden" name="smartpay_selected_currency_symbol" value="<?php echo esc_attr(smartpay_get_currency_symbol()); ?>">
        <!-- /Form Data -->

        <!-- Payment modal -->
        <?php include  __DIR__ . '/payment_modal.php'; ?>
    </div>
</div>
