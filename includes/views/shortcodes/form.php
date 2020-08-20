<?php
$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$_gateway = \sanitize_text_field($_REQUEST['gateway'] ?? '');

$chosen_gateway = isset($_gateway) && smartpay_is_gateway_active($_gateway) ? $_gateway : smartpay_get_default_gateway();
$has_payment_error = false;

$form_suggested_amount = is_array($form_amounts) ? intval(array_sum($form_amounts) / (count($form_amounts) ? count($form_amounts) : 1)) : $form_amount ?? '';
?>

<?php if ('embedded' == $behavior) : ?>
    <div class="smartpay">
        <div class="smartpay-form-shortcode smartpay-payment">
            <!-- Form details -->
            <div class="card form">
                <?php if ($form->image) : ?>
                    <div class="bg-light form--image border-bottom">
                        <img src="<?php echo $form->image; ?>" class="card-img-top">
                    </div>
                <?php endif; ?>

                <div class="card-body p-5">
                    <div class="row">
                        <div class="col-sm-12 col-md-7 mb-3">
                            <?php if ($form->title) : ?>
                                <h2 class="card-title form--title mt-0 mb-2"><?php echo $form->title; ?></h2>
                            <?php endif; ?>

                            <?php if ($form->description) : ?>
                                <div class="card-text form--description">
                                    <?php echo $form->description; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-12 col-md-5">
                            <div class="form--amount-section">
                                <div class="form-amounts mb-2">
                                    <?php
                                    $form_amounts = $form->get_amounts() ?? [];
                                    $form_amount = reset($form_amounts) ?? 0;
                                    ?>
                                    <label class="form-amounts--label d-block m-0 mb-2"><?php _e('Select an amount', 'smartpay'); ?></label>
                                    <ul class="list-group m-0">
                                        <?php if ($form->has_multiple_amount()) : ?>
                                            <!-- // Multiple amounts -->
                                            <?php foreach ($form_amounts as $index => $amount) : ?>
                                                <li class="list-group-item amount form--fixed-amount m-0 mb-3 py-4 <?php echo 0 == $index ? 'selected' : ''; ?>">
                                                    <label for="_form_amount_<?php echo esc_attr($index); ?>" class="d-block m-0">
                                                        <input class="d-none" type="radio" name="_form_amount" id="_form_amount_<?php echo esc_attr($index); ?>" value="<?php echo esc_attr($amount); ?>" <?php echo 0 == $index ? 'checked' : ''; ?>>
                                                        <h5 class="m-0 amount--title"><?php echo smartpay_amount_format($amount); ?></h5>
                                                    </label>
                                                </li>

                                            <?php endforeach; ?>

                                            <!-- Form amount -->
                                        <?php else : ?>
                                            <li class="list-group-item amount m-0 mb-3 py-4 selected">
                                                <label for="_form_amount" class="d-block m-0">
                                                    <input class="d-none" type="radio" name="_form_amount" id="_form_amount" value="<?php echo esc_attr($form_amount); ?>" checked>
                                                    <h5 class="m-0"><?php echo smartpay_amount_format($form_amount); ?></h5>
                                                </label>
                                            </li>
                                        <?php endif; ?>
                                    </ul>

                                    <!-- // Allow custom payment -->
                                    <div class="form-group custom-amount-wrapper my-4 <?php echo !$form->allow_custom_amount ? 'd-none' : '' ?>">
                                        <label for="smartpay_custom_amount" class="form-amounts--label d-block m-0 mb-2"><?php _e('Pay custom amount', 'smartpay'); ?></label>
                                        <input type="text" class="form-control form--custom-amount amount" id="smartpay_custom_amount" name="smartpay_form_amount" value="<?php echo esc_attr($form_amount); ?>" placeholder="<?php echo esc_attr($form_suggested_amount); ?>">
                                    </div>
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
            <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="form_payment">
            <input type="hidden" name="smartpay_form_id" id="smartpay_form_id" value="<?php echo $form->ID ?? 0; ?>">
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
                                <div class="smartpay-form-shortcode smartpay-payment">
                                    <!-- Form details -->
                                    <div class="card form">
                                        <?php if ($form->image) : ?>
                                            <div class="bg-light form--image border-bottom">
                                                <img src="<?php echo $form->image; ?>" class="card-img-top">
                                            </div>
                                        <?php endif; ?>

                                        <div class="card-body p-5">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-7 mb-3">
                                                    <?php if ($form->title) : ?>
                                                        <h2 class="card-title form--title mt-0 mb-2"><?php echo $form->title; ?></h2>
                                                    <?php endif; ?>

                                                    <?php if ($form->description) : ?>
                                                        <div class="card-text form--description">
                                                            <?php echo $form->description; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-sm-12 col-md-5">
                                                    <div class="form--amount-section">
                                                        <div class="form-amounts mb-2">
                                                            <?php
                                                            $form_amounts = $form->get_amounts() ?? [];
                                                            $form_amount = reset($form_amounts) ?? 0;
                                                            ?>
                                                            <label class="form-amounts--label d-block m-0 mb-2"><?php _e('Select an amount', 'smartpay'); ?></label>
                                                            <ul class="list-group m-0">
                                                                <?php if ($form->has_multiple_amount()) : ?>
                                                                    <!-- // Multiple amounts -->
                                                                    <?php foreach ($form_amounts as $index => $amount) : ?>
                                                                        <li class="list-group-item amount form--fixed-amount m-0 mb-3 py-4 <?php echo 0 == $index ? 'selected' : ''; ?>">
                                                                            <label for="_form_amount_<?php echo esc_attr($index); ?>" class="d-block m-0">
                                                                                <input class="d-none" type="radio" name="_form_amount" id="_form_amount_<?php echo esc_attr($index); ?>" value="<?php echo esc_attr($amount); ?>" <?php echo 0 == $index ? 'checked' : ''; ?>>
                                                                                <h5 class="m-0 amount--title"><?php echo smartpay_amount_format($amount); ?></h5>
                                                                            </label>
                                                                        </li>

                                                                    <?php endforeach; ?>

                                                                    <!-- Form amount -->
                                                                <?php else : ?>
                                                                    <li class="list-group-item amount m-0 mb-3 py-4 selected">
                                                                        <label for="_form_amount" class="d-block m-0">
                                                                            <input class="d-none" type="radio" name="_form_amount" id="_form_amount" value="<?php echo esc_attr($form_amount); ?>" checked>
                                                                            <h5 class="m-0"><?php echo smartpay_amount_format($form_amount); ?></h5>
                                                                        </label>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>

                                                            <!-- // Allow custom payment -->
                                                            <div class="form-group custom-amount-wrapper my-4 <?php echo !$form->allow_custom_amount ? 'd-none' : '' ?>">
                                                                <label for="smartpay_custom_amount" class="form-amounts--label d-block m-0 mb-2"><?php _e('Pay custom amount', 'smartpay'); ?></label>
                                                                <input type="text" class="form-control form--custom-amount amount" id="smartpay_custom_amount" name="smartpay_form_amount" value="<?php echo esc_attr($form_amount); ?>" placeholder="<?php echo esc_attr($form_suggested_amount); ?>">
                                                            </div>
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
                                    <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="form_payment">
                                    <input type="hidden" name="smartpay_form_id" id="smartpay_form_id" value="<?php echo $form->ID ?? 0; ?>">
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
                <?php echo _e($label ?: 'Pay now', 'smartpay'); ?>
            </button>
        </div>
    </div>
<?php endif; ?>