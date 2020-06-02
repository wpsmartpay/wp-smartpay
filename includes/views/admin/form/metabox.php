<div class="smartpay" id="smartpay-metabox" style="margin: -6px -12px -12px -12px;">

    <div class="smartpay-form-metabox p-3">
        <!-- Payment Type -->
        <div class="payment-type">
            <label class="text-muted my-2 d-block"><strong>Payment Type</strong></label>
            <ul class="list-group list-group-horizontal-sm">
                <li class="list-group-item d-flex justify-content-between">
                    <div class="custom-checkbox custom-checkbox-round">
                        <input type="radio" class="custom-control-input" id="payment_type_one-time" name="payment_type" value="one-time" checked>
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

        <!-- Fixed amount container -->
        <div class="fixed-amount d-none">
            <div class="form-row">
                <div class="col">
                    <div class="form-group">
                        <label for="amount" class="text-muted my-2 d-block"><strong>Amount</strong></label>
                        <input type="text" class="form-control" id="amount" name="amount" value="<?php echo $amount; ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Multiple amount container -->
        <div class="multiple-amount">
            <label for="amount" class="text-muted my-2 d-block"><strong>Amounts</strong></label>
            <div class="form-row">
                <div class="col-sm-2">
                    <div class="form-group">
                        <input type="text" class="form-control" id="amount" name="amount" value="<?php echo $amount; ?>" placeholder="5.0">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <input type="text" class="form-control" id="amount" name="amount" value="<?php echo $amount; ?>" placeholder="7.0">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary">Add more</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Allow custom amount container -->
        <div class="allow-custom-amount">
            <label for="amount" class="text-muted my-2 d-block"><strong>Custom Amount</strong></label>
            <div class="row">
                <div class="col-6">
                    <div class="card m-0 d-flex justify-content-between p-2">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="allow_custom" value="0">
                            <input type="checkbox" class="custom-control-input" id="allow_custom" name="allow_custom" value="1"><label class="custom-control-label" for="allow_custom">Allow Custom Amount</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>