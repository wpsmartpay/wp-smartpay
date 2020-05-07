<div class="smartpay smartpay_pricing" id="smartpay_pricing_section">
    <div class="form-group">
        <label for="amount">Amount</label>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">$</span>
            </div>
            <input type="text" name="amount" id="amount" class="form-control" placeholder="1.0">
        </div>
    </div>

    <div class="custom-amount">
        <div class="form-group d-flex justify-content-between">
            <label for="customAmount" class="col-form-label">Allow customers to pay what they want</label>
            <div class="text-right">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="customAmount">
                    <label class="custom-control-label" for="customAmount"></label>
                </div>
            </div>
        </div>

        <div class="card">
            <dic class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="min_amount">Minimum amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" name="min_amount" id="min_amount" class="form-control" placeholder="1.0">
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="suggested_amount">Suggested amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" name="suggested_amount" id="suggested_amount" class="form-control" placeholder="1.0">
                            </div>
                        </div>
                    </div>
                </div>
            </dic>
        </div>
    </div>
</div>