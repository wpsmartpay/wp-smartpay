<div class="smartpay smartpay_variant" id="smartpay_variant_section">
    <div class="form-group">
        <div class="form-group d-flex justify-content-between">
            <label for="customAmount" class="col-form-label">Product has variant</label>
            <div class="text-right">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="customAmount">
                    <label class="custom-control-label" for="customAmount"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="card" id="add_variant_section">
        <div class="card-header p-0">
            <div class="d-flex">
                <input type="text" class="form-control border-0" id="variant_name[0]" name="variant_name[0]" placeholder="Variant name">
                <button type="button" class="btn btn-light">Delete</button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex">
                <div class="form-group w-100">
                    <label for="option_name[0]">Option name</label>
                    <input type="text" class="form-control" id="option_name[0]" name="option_name[0]" placeholder="Option name">
                </div>
                <button type="button" class="btn btn-light">Delete</button>
            </div>

            <div class="form-group">
                <label for="option_description[0]">Description</label>
                <textarea class="form-control" id="option_description[0]" rows="3"></textarea>
            </div>

            <div class="additional-data">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="add_amount[0]">Additional amount</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" name="add_amount[0]" id="add_amount[0]" class="form-control" placeholder="1.0">
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="quantity[0]">Quantity</label>
                            <input type="text" name="quantity[0]" id="quantity[0]" class="form-control" placeholder="∞">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <button class="btn btn-lg btn-primary">Select files</button>
            </div>


            <?php require 'contents_meta_box.php'; ?>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-sm btn-primary add-variant">Add variant</button>
    </div>
</div>