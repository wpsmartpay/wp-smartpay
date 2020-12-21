<div class="smartpay" style="margin: 0 auto; background: transparent">
    <div class="smartpay-form-shortcode smartpay-payment">
        <div class="card form bg-transparent border-0">
            <div class="card-body smartpay_form_builder_wrapper p-5">
                <?php $action = '' ?>
                <form action="" method="POST">
                    <?php echo do_blocks($form->body);
                    ?>


                    <div class="form--amount-section">
                        <label class="form-amounts--label d-block m-0 mb-2"><?php _e('Select an amount', 'smartpay') ?></label>
                        <div class="form-amounts">
                            <?php foreach ($form->amounts as $amount) : ?>
                            <div class="amount form--fixed-amount">
                                <label for="_form_amount_<?php echo $amount['key']; ?>" class="d-block m-0">
                                    <input class="d-none" type="radio" name="_form_amount" id="_form_amount_<?php echo $amount['key']; ?>" value="<?php echo $amount['amount']; ?>">
                                    <p class="m-0 amount--title"><?php echo $amount['label']; ?> - <?php echo smartpay_amount_format($amount['amount']); ?></p>
                                </label>
                            </div>
                            <?php endforeach; ?>

                            <?php if ($form->settings['allowCustomAmount']) : ?>
                            <!-- // Allow custom payment -->
                            <div class="form-group custom-amount-wrapper m-0 ">
                                <label for="smartpay_custom_amount" class="form-amounts--label d-block m-0 mb-2">
                                    <?php echo $form->settings['customAmountLabel']; ?></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text px-3" id="default-currency"><?php echo smartpay_get_currency_symbol() ?></span>
                                    </div><input type="text" class="form-control form--custom-amount amount" id="smartpay_custom_amount" name="smartpay_form_amount" value="10" placeholder="">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- <button type="button" class="btn btn-success btn-block btn-lg open-payment-form"> -->
                        <!-- Pay </button> -->
                    </div>
                </form>
            </div>
        </div>

        <!-- Form Data -->
        <input type="hidden" name="smartpay_payment_type" id="smartpay_payment_type" value="form_payment">
        <input type="hidden" name="smartpay_form_id" id="smartpay_form_id" value="<?php echo $form->id ?? 0; ?>">
        <!-- /Form Data -->

        <!-- Payment modal -->
        <?php include  __DIR__ . '/payment_modal.php'; ?>
    </div>
</div>