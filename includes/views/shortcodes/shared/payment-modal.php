<div class="modal fade payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content align-content-between">
            <div class="text-center p-4 pb-5">
                <div class="mt-2">
                    <h4 class="payment-modal--small-title m-0 mb-3">
                        <?php echo __('Make a Payment', 'smartpay'); ?>
                    </h4>
                    <h2 class="payment-modal--title m-0 mb-3">
                        <?php echo $product->title ?? 'Product/Form'; ?>
                    </h2>
                    <p class="payment-modal--subtitle m-0 mb-3"><?php _e('Enter your info and complete payment!', 'smartpay'); ?></p>
                </div>
            </div>

            <div class="modal-body text-center mb-4 step-1">
                <div class="col-10 col-lg-7 text-center pb-4">
                    <form action="<?php echo $form_action; ?>" method="POST">
                        <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>

                        <div class="payment-modal--gateway">
                            <!-- // If only one gateway activated -->
                            <?php if (count($gateways) == 1) : ?>
                            <?php $gateways_index = array_keys($gateways); ?>
                            <p class="payment-gateway--label single-gateway"><?php echo sprintf(__('Pay with ', 'smartpay') . ' <strong>%s</strong>', esc_html(reset($gateways)['checkout_label'])); ?></p>
                            <input type="hidden" name="smartpay_gateway" id="smartpay_gateway" value="<?php echo esc_html(reset($gateways_index)); ?>">

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

            </div>
            <div class="modal-body text-center mb-4 p-4 step-2">
                <div class="my-3 col mb-5">
                    <div class="alert alert-danger">
                        <p class="m-0"><?php _e('Don\'t close this window', 'smartpay'); ?></p>
                    </div>
                </div>
                <div class="dynamic-content">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>