<?php
$variations = $download->get_variations() ?? [];
// var_dump($variations);
?>

<div class="smartpay smartpay_variant" id="smartpay_variant_section">

    <!-- Add Variant -->
    <div class="border rounded bg-light text-center p-5">
        <i data-feather="layers" width="42" height="42"></i>
        <h3>Offer variations of this product</h3>
        <p class="text-muted">Sweeten the deal for your customers with different options for format, version, etc</p>
        <button class="btn btn-light border shadow-sm" id="add_variation">Add Variations</button>
    </div>

    <div class="card p-0" id="add_variant_section">
        <div class="card-header bg-white p-0">
            <div class="d-flex">
                <input type="text" class="form-control border-0" id="variations[0][title]" name="variations[0][title]" placeholder="Variant name" value="<?php echo $variations[0] ? $variations[0]['title'] : ''; ?>">
                <button type="button" class="btn btn-light border btn-sm my-1 mr-2 pb-0 shadow-sm">
                    <i data-feather="trash" width="16" height="16"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Variant start -->
            <div class="variant-option">
                <div class="variant-option__header p-3">
                    <div class="form-row">
                        <div class="col-7">
                            <div class="form-group m-0">
                                <label for="variations[0][name]" class="text-muted my-2 d-block"><strong>Option name</strong></label>
                                <input type="text" class="form-control" id="variations[0][name]" name="variations[0][name]" placeholder="Option name" value="<?php echo $variations[0] ? $variations[0]['name'] : ''; ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group m-0">
                                <label for="variations[0][additional_amount]" class="text-muted my-2 d-block"><strong>Additional amount</strong></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><?php echo smartpay_get_currency_symbol() ?></span></div>
                                    <input type="text" name="variations[0][additional_amount]" id="variations[0][additional_amount]" class="form-control" placeholder="1.0" value="<?php echo $variations[0] ? $variations[0]['additional_amount'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col d-flex align-items-center">
                            <div class="mt-4">
                                <button type="button" class="btn btn-light btn-sm border shadow-sm pb-0"><i data-feather="edit-3" width="20" height="20"></i></button>
                                <button type="button" class="btn btn-light btn-sm border shadow-sm pb-0 ml-2"><i data-feather="trash" width="20" height="20"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="variant-option-body bg-light p-3">
                    <div class="form-group">
                        <label for="variations[0][description]" class="text-muted my-2 d-block"><strong>Description</strong></label>
                        <textarea class="form-control" id="variations[0][description]" name="variations[0][description]" rows="3"><?php echo $variations[0] ? $variations[0]['description'] : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="text-muted my-2 d-block"><strong>Files</strong></label>
                        <div class="border rounded text-center p-5">
                            <i data-feather="package" width="42" height="42"></i>
                            <h3 class="text-muted">Associate files with this variant</h3>
                            <button class="btn btn-light border shadow-sm">Select files</button>
                        </div>
                    </div>

                    <!-- Files selection -->
                    <ul class="list-group">
                        <li class="list-group-item m-0 d-flex justify-content-between">
                            <div class="custom-checkbox custom-checkbox-round">
                                <input type="checkbox" class="custom-control-input" id="variations[0][files][0]" name="variations[0][files][]" value="file 1" checked>
                                <label class="custom-control-label" for="variations[0][files][0]">Cras justo odio</label>
                            </div>
                        </li>
                        <li class="list-group-item m-0 d-flex justify-content-between">
                            <div class="custom-checkbox custom-checkbox-round">
                                <input type="checkbox" class="custom-control-input" id="variations[0][files][1]" name="variations[0][files][]" value="file 3" checked>
                                <label class="custom-control-label" for="variations[0][files][1]">Cras justo odio</label>
                            </div>
                        </li>
                        <li class="list-group-item m-0 d-flex justify-content-between">
                            <div class="custom-checkbox custom-checkbox-round">
                                <input type="checkbox" class="custom-control-input" id="variations[0][files][3]" name="variations[0][files][]" value="file 4">
                                <label class="custom-control-label" for="variations[0][files][3]">Cras justo odio</label>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
            <!-- Variant end -->
        </div>

        <div class="card-footer bg-white p3">
            <button class="btn btn-secondary add-variant">Add option</button>
        </div>
    </div>
</div>