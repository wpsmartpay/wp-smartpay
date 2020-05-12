<?php
$variations = $product->get_variations() ?? [];
// var_dump($variations);
?>

<div class="smartpay-variations">
    <!-- Add variations -->
    <div class="border rounded bg-light text-center p-5 no-variations-box"
        <?php echo count($variations) ? 'style="display:none"': '' ?>>
        <i data-feather="layers" width="42" height="42"></i>
        <h3>Offer variations of this product</h3>
        <p class="text-muted">Sweeten the deal for your customers with different options for format, version, etc</p>
        <button type="button" class="btn btn-light border shadow-sm add-variation">Add Variations</button>
    </div>

    <div class="card p-0 variations-secion" <?php echo !count($variations) ? 'style="display:none"': '' ?>>
        <input type="hidden" name="has_variations" id="has_variations" value="<?php echo count($variations) ? 1 : 0 ?>">
        <div class="card-header bg-white p-0">
            <div class="d-flex px-3 py-2">
                <h3 class="m-0 pt-1 d-flex">Variations</h3>
                <button type="button" class="btn btn-light border btn-sm my-1 ml-auto pb-0 shadow-sm remove-variation">
                    <i data-feather="trash" width="16" height="16"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0 variations">
            <!-- Variantions -->
            <?php foreach ($variations as $index => $variation) : ?>
            <?php $variation_id = $variation['id'] ?? $index + 1; ?>
            <div class="variation-option" data-variation-id="<?php echo $variation_id; ?>">
                <div class="variation-option__header p-3">
                    <div class="form-row">
                        <div class="col-7">
                            <div class="form-group m-0">
                                <label for="<?php echo 'variations[' . $variation_id . '][name]'; ?>"
                                    class="text-muted my-2 d-block"><strong>Option
                                        name</strong></label>
                                <input type="text" class="form-control"
                                    id="<?php echo 'variations[' . $variation_id . '][name]'; ?>"
                                    name="<?php echo 'variations[' . $variation_id . '][name]'; ?>"
                                    placeholder="Option name" value="<?php echo $variation['name'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group m-0">
                                <label for="<?php echo 'variations[' . $variation_id . '][additional_amount]'; ?>"
                                    class="text-muted my-2 d-block"><strong>Additional amount</strong></label>
                                <input type="text"
                                    name="<?php echo 'variations[' . $variation_id . '][additional_amount]'; ?>"
                                    id="<?php echo 'variations[' . $variation_id . '][additional_amount]'; ?>"
                                    class="form-control" placeholder="1.0"
                                    value="<?php echo $variation['additional_amount'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="col d-flex align-items-center">
                            <div class="mt-4">
                                <!-- <button type="button" class="btn btn-light btn-sm border shadow-sm pb-0"><i
                                        data-feather="edit-3" width="20" height="20"></i></button> -->
                                <button type="button"
                                    class="btn btn-light btn-sm border shadow-sm pb-0 ml-2 remove-variation-option"><i
                                        data-feather="trash" width="20" height="20"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="variation-option-body bg-light p-3">
                    <div class="form-group">
                        <label for="<?php echo 'variations[' . $variation_id . '][description]'; ?>"
                            class="text-muted my-2 d-block"><strong>Description</strong></label>
                        <textarea class="form-control"
                            id="<?php echo 'variations[' . $variation_id . '][description]'; ?>"
                            name="<?php echo 'variations[' . $variation_id . '][description]'; ?>"
                            rows="3"><?php echo $variation ? $variation['description'] : ''; ?></textarea>
                    </div>

                    <!-- Files -->
                    <label class="text-muted my-2 d-block"><strong>Files</strong></label>
                    <div class="form-group no-variation-file-box">
                        <div class="border rounded text-center p-5">
                            <i data-feather="package" width="42" height="42"></i>
                            <h3 class="text-muted">Associate files with this variant</h3>
                            <button type="button" class="btn btn-light border shadow-sm select-variation-files">Select
                                files</button>
                        </div>
                    </div>
                    <div class="variation-files-secion" style="display:none">
                        <ul class="list-group variation-files">
                            <!-- <li class="list-group-item m-0 d-flex justify-content-between files-item">
                                <div class="custom-checkbox custom-checkbox-round">
                                    <input type="checkbox" class="custom-control-input variation-file"
                                        id="<?php echo 'variations[' . $variation_id . '][files][0]'; ?>"
                                        name="<?php echo 'variations[' . $variation_id . '][files][0]'; ?>"
                                        value="file 1" checked>
                                    <label class="custom-control-label"
                                        for="<?php echo 'variations[' . $variation_id . '][files][0]'; ?>">File
                                        Name</label>
                                </div>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer bg-white p3">
            <button class="btn btn-secondary add-variation-option">Add option</button>
        </div>
    </div>
</div>