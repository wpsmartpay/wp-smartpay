<?php

use SmartPay\Models\Customer;

$customer = is_user_logged_in() ? Customer::where('id', get_current_user_id())->first() : null;
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
                    <p class="payment-modal--small-title mb-2"><?php echo $product->title ?? $form->title ?? 'Product/Form'; ?></p>
                    <h2 class="payment-modal--title amount m-0">0</h2>
                </div>

                <button class="btn modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body p-1 text-center step-1">
                <div class="align-self-center">
                    <form action="<?php echo $form_action; ?>" method="POST">
                        <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>

                        <div class="payment-modal--gateway">
                            <!-- // If only one gateway activated -->
                            <?php if (count($gateways) == 1) : ?>
                            <?php $gateways_index = array_keys($gateways); ?>
                            <!-- <p class="payment-gateway--label text-muted single-gateway"> -->
                            <?php //echo sprintf(__('Payment method - ', 'smartpay') . ' <strong>%s</strong>', esc_html(reset($gateways)['checkout_label'])); 
                                ?>
                            <!-- </p> -->
                            <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway" value="<?php echo esc_html(reset($gateways_index)); ?>" checked>

                            <!-- // If it has multiple payment gateway -->
                            <?php elseif (count($gateways) > 1) : ?>
                            <p class="payment-gateway--label text-muted"><?php echo _e('Select a payment method', 'smartpay'); ?></p>

                            <div class="gateways m-0 justify-content-center d-flex">
                                <?php foreach ($gateways as $gateway_id => $gateway) : ?>
                                <div class="gateway">
                                    <input type="radio" class="d-none" name="smartpay_gateway" id="<?php echo 'smartpay_gateway_' . esc_attr($gateway_id); ?>" value="<?php echo esc_attr($gateway_id) ?>" <?php echo checked($gateway_id, $chosen_gateway, false); ?>>
                                    <label for="<?php echo 'smartpay_gateway_' . esc_attr($gateway_id); ?>" class="gateway--label">
                                        <img src="<?php echo SMARTPAY_PLUGIN_ASSETS . '/img/' . $gateway_id . '.png'; ?>" alt="">
                                        <!-- <?php echo esc_html($gateway['checkout_label']); ?> -->
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else : ?>

                            <?php
                                $has_payment_error = true;
                                echo 'You must enable a payment gateway to proceed a payment.';
                                ?>
                            <?php endif; ?>
                        </div>

                        <div class="payment-modal--errors" style="display: none"></div>

                        <div class="payment-modal--user-info mt-3">
                            <div class="form-row">
                                <div class="col-sm-6 form-group">
                                    <input type="text" placeholder="First name" class="form-control" name="smartpay_first_name" id="smartpay_first_name" value="<?php echo $customer->first_name ?? ''; ?>" autocomplete="first_name" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <input type="text" placeholder="Last name" class="form-control" name="smartpay_last_name" id="smartpay_last_name" value="<?php echo $customer->last_name ?? ''; ?>" autocomplete="last_name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="Email address" class="form-control" name="smartpay_email" id="smartpay_email" value="<?php echo $customer->email ?? ''; ?>" autocomplete="email" required>
                            </div>

                            <button type="button" class="btn btn-success btn-block btn-lg smartpay-pay-now" <?php if ($has_payment_error) echo 'disabled'; ?>>
                                <?php echo _e('Pay Now', 'smartpay'); ?>
                            </button>

                            <?php $gateways_index = array_keys($gateways); ?>
                            <?php if (count($gateways) == 1 && 'paddle' == reset($gateways_index)) : ?>
                            <div class="mt-3"><img src="<?php echo SMARTPAY_PLUGIN_ASSETS . '/img/paddle-payment-methods.png'; ?>" class="img-fluid" alt="paddle-payment-methods"></div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

            </div>

            <div class="modal-body p-1 text-center step-2">
                <div class="align-self-center">
                    <div class="mb-5">
                        <div class="alert alert-warning py-3">
                            <p class="m-0"><?php _e('Don\'t close this window before completing payment!', 'smartpay'); ?></p>
                        </div>
                    </div>
                    <div class="dynamic-content">
                        <div class="spinner-border" style="width: 40px; height: 40px;">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-loading justify-content-center align-items-center">
                <div class="spinner-border text-secondary" style="width: 40px; height: 40px;">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <div id="smartpay_currency_symbol" data-value="<?php //echo smartpay_get_currency_symbol(); 
                                                    ?>"></div> -->
<div id="smartpay_currency_symbol" data-value="USD"></div>