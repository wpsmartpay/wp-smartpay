<?php

use SmartPay\Models\Customer;

$customer = is_user_logged_in() ? Customer::where('user_id', get_current_user_id())->first() : null;

$gateways = smartpay_get_enabled_payment_gateways(true);

$manual_gateways = smartpay_payment_gateways();
$free_gateway = $manual_gateways['free'];
$_gateway = \sanitize_text_field($_REQUEST['gateway'] ?? '');

$chosen_gateway = isset($_gateway) && smartpay_is_gateway_active($_gateway) ? $_gateway : smartpay_get_default_gateway();
$has_payment_error = false;
?>

<div class="modal fade payment-modal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header mx-auto">
                <button class="btn back-to-first-step">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <div class="d-flex flex-column justify-content-center modal-title">
                    <p class="payment-modal--small-title mb-2 text-capitalize"><?php echo esc_html($product->title ?? $form->title ?? 'Product/Form'); ?></p>
                    <h2 class="payment-modal--title amount m-0">--</h2>
                </div>


                <button class="btn modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="payment-modal--errors text-center" style="display: none"></div>

            <?php do_action('smartpay_product_modal_popup_content', $product); ?>

            <div class="modal-body p-1 text-center step-1">
                <div class="align-self-center w-100">
                    <form action="<?php echo esc_url(smartpay_get_payment_page_uri()); ?>" method="POST">
                        <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>
                        <div class="payment-modal--gateway">
                            <!-- // If Product has Zero sale amount -->
                            <?php
                                //FIXME: gateways are not visible if the main product is free or sale amount is zero
                            ?>
                            <?php if ($product->sale_price <= 0) : ?>
                                <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway" value="free" checked>

                                <!-- // If only one gateway activated -->
                            <?php elseif (count($gateways) == 1) : ?>
                                <?php $gateways_index = array_keys($gateways); ?>
                                <p class="payment-gateway--label text-muted single-gateway">
                                    <?php echo wp_kses_post(sprintf(__('Payment method - ', 'smartpay') . ' <strong>%s</strong>', esc_html(reset($gateways)['checkout_label'])));
                                    ?>
                                </p>
                                <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway" value="<?php echo esc_attr(reset($gateways_index)); ?>" checked>

                                <!-- // If it has multiple payment gateway -->
                            <?php elseif (count($gateways) > 1) : ?>
                                <div class="gateways m-0 justify-content-center d-flex">
                                    <?php foreach ($gateways as $gatewayId => $gateway) : ?>
                                        <div class="gateway">
                                            <input type="radio" class="d-none" name="smartpay_gateway" id="<?php echo 'smartpay_gateway_' . esc_attr($gatewayId); ?>" value="<?php echo esc_attr($gatewayId) ?>" <?php echo checked($gatewayId, $chosen_gateway, false); ?>>
                                            <label for="<?php echo 'smartpay_gateway_' . esc_attr($gatewayId); ?>" class="gateway--label">
                                                <!-- dynamically load the gateway image -->
                                                <img src="<?php echo esc_url($gateway['gateway_icon']); ?>" alt="<?php echo esc_attr($gateway['checkout_label']); ?>">
                                                <!-- <?php echo esc_html($gateway['checkout_label']); ?> -->
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <?php $has_payment_error = true; ?>
                                <div class="alert alert-danger"><?php echo esc_html__('You must enable a payment gateway to proceed a payment.', 'smartpay'); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="payment-modal--user-info">
                            <div class="form-row">
                                <div class="col-sm-6 form-group">
                                    <input type="text" placeholder="First name" class="form-control" name="smartpay_first_name" id="smartpay_first_name" value="<?php echo esc_attr($customer->first_name ?? ''); ?>" autocomplete="first_name" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <input type="text" placeholder="Last name" class="form-control" name="smartpay_last_name" id="smartpay_last_name" value="<?php echo esc_attr($customer->last_name ?? ''); ?>" autocomplete="last_name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="Email address" class="form-control" name="smartpay_email" id="smartpay_email" value="<?php echo esc_attr($customer->email ?? ''); ?>" autocomplete="email" required>
                            </div>
                            <div id="mobile-field"></div>

                            <?php do_action('smartpay_before_product_payment_form_button', $product); ?>

                            <button type="button" class="btn btn-success btn-block btn-lg smartpay-pay-now" <?php if ($has_payment_error) echo 'disabled'; ?>>
                                <?php echo esc_html__('Pay Now', 'smartpay'); ?>
                            </button>

                            <?php do_action('smartpay_after_product_payment_form_button', $product); ?>
                        </div>
                    </form>
                </div>

            </div>

            <div class="modal-body p-1 text-center step-2">
                <div class="align-self-center">
                    <div class="mb-5">
                        <div class="alert alert-warning py-3">
                            <p class="m-0"><?php echo esc_html__('Don\'t close this window before completing payment!', 'smartpay'); ?></p>
                        </div>
                    </div>
                    <div class="dynamic-content">
                        <div class="spinner-border" style="width: 40px; height: 40px;">
                            <span class="sr-only"><?php echo esc_html__('Loading', 'smartpay'); ?>...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-loading justify-content-center align-items-center">
                <div class="spinner-border text-secondary" style="width: 40px; height: 40px;">
                    <span class="sr-only"><?php echo esc_html__('Loading', 'smartpay'); ?>...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!--<div id="smartpay_currency_symbol" data-value="$"></div>-->
