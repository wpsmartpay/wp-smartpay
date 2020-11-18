<div class="smartpay" style="margin: 0 auto;">
    <div class="smartpay-form-shortcode smartpay-payment">
        <div class="card form">
            <div class="card-body p-5">
                <?php $action = '' ?>
                <form action="" method="POST">
                    <?php echo $form->body; ?>
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