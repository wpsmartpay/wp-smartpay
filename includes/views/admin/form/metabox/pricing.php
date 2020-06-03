<div class="smartpay-form-metabox">
    <!-- Payment Type -->
    <div class="payment-type-secion">
        <label class="text-muted my-2 d-block"><strong>Payment Type</strong></label>
        <ul class="list-group list-group-horizontal-sm">
            <li class="list-group-item d-flex justify-content-between">
                <div class="custom-checkbox custom-checkbox-round">
                    <input type="radio" class="custom-control-input" id="payment_type_one-time" name="payment_type" value="one-time" <?php echo 'checked'; ?>>
                    <label class="custom-control-label" for="payment_type_one-time">One-Time</label>
                </div>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <div class="custom-checkbox custom-checkbox-round">
                    <input type="radio" class="custom-control-input" id="payment_type__" name="payment_type" value="_" disabled>
                    <label class="custom-control-label" for="payment_type__">Recurring (Available on Pro)</label>
                </div>
            </li>
        </ul>
    </div>

    <!-- Form amounts -->
    <div class="form-amounts-secion mt-4">
        <label for="amounts" class="text-muted my-2 d-block"><strong>Amounts</strong></label>
        <div id="form-amounts" class="form-row">
            <?php if (count($form->amounts ?? [])) : ?>
            <?php foreach ($form->amounts as $index => $amount) : ?>
            <div class="col-sm-2 amount-section mb-3">
                <div class="input-group">
                    <input type="text" class="form-control amount" id="<?php echo 'amounts[' . $index . ']'; ?>" name="<?php echo 'amounts[' . $index . ']'; ?>" value="<?php echo $amount; ?>" placeholder="5.0">
                    <?php if ($index >= 1) : ?>
                    <div class="input-group-append">
                        <button class="btn btn-light border remove-amount" type="button"><i data-feather="x" width="17" height="17"></i></button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else : ?>
            <div class="col-sm-2 amount-section mb-3">
                <div class="form-group">
                    <input type="text" class="form-control amount" id="amounts[0]" name="amounts[0]" placeholder="5.0">
                </div>
            </div>
            <?php endif; ?>

        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <button type="button" id="add-more-amount" class="btn btn-secondary btn-sm">Add more</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Allow custom amount container -->
    <div class="allow-custom-amount mt-4">
        <label for="amount" class="text-muted my-2 d-block"><strong>Custom Amount</strong></label>
        <div class="row">
            <div class="col-6">
                <div class="card m-0 d-flex justify-content-between p-2">
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="allow_custom_amount" value="0">
                        <input type="checkbox" class="custom-control-input" id="allow_custom_amount" name="allow_custom_amount" value="1" <?php echo $form->allow_custom_amount ? 'checked' : ''; ?>><label class="custom-control-label" for="allow_custom_amount">Allow Custom Amount</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>