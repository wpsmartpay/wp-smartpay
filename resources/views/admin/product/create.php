<div class="smartpay">
    <div class="container-full">
        <div class="text-black bg-white border-bottom">
            <div class="container">
                <div class="wrap d-none">
                    <h2></h2>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="text-black"><?php _e('Create Product', 'smartpay'); ?></h2>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary px-3"><?php _e('Publish', 'smartpay'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="mt-3">


                <form id="create-product" action="<?php echo admin_url('admin.php?page=smartpay-products&action=store') ?>" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $product->title ?? '' ?>" placeholder="Product title">
                    </div>

                    <?php
                    wp_editor('', 'unique_id', array(
                        'textarea_rows' => 10,
                    ));
                    ?>


                    <!-- <div class="form-group">
                        <textarea class="form-control" id="description" name="description" placeholder="Description" rows="5"></textarea>
                    </div> -->

                    <div id="smartpay-metabox">
                        <ul class="nav nav-tabs  nav-fill" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Files</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Pricing</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="product-files-secion" <?php echo !count($product->files) ? 'style="display:none"' : '' ?>>
                                    <ul class="list-group product-files" id="product-files">
                                        <?php foreach ($product->files as $index => $file) : ?>
                                        <?php $id = $file->id ?? $index + 1; ?>
                                        <li class="list-group-item list-group-item-action mb-0 files-item" id="file-<?php echo $id ?>" data-file-id="<?php echo $id ?>">
                                            <input type="hidden" class="form-control file-id" name="<?php echo 'files[' . $id . '][id]'; ?>" value="<?php echo $id ?>">
                                            <input type="hidden" class="form-control file-icon" name="<?php echo 'files[' . $id . '][icon]'; ?>" value="<?php echo $file->icon ?? '' ?>">
                                            <input type="hidden" class="form-control file-filename" name="<?php echo 'files[' . $id . '][filename]'; ?>" value="<?php echo $file->filename ?? '' ?>">
                                            <input type="hidden" class="form-control file-mime" name="<?php echo 'files[' . $id . '][mime]'; ?>" value="<?php echo $file->mime ?? '' ?>">
                                            <input type="hidden" class="form-control file-size" name="<?php echo 'files[' . $id . '][size]'; ?>" value="<?php echo $file->size ?? '' ?>">
                                            <input type="hidden" class="form-control file-url" name="<?php echo 'files[' . $id . '][url]'; ?>" value="<?php echo $file->url ?? '' ?>">

                                            <div class="d-flex">
                                                <div class="file-type">
                                                    <img src="<?php echo $file->icon ?? '#' ?>" alt="<?php echo $file->mime ?? '-' ?>" width="28" height="28" />
                                                </div>
                                                <div class="d-flex justify-content-between w-100">
                                                    <div class="d-flex flex-column ml-3">
                                                        <h5 class="file-name m-0"><?php echo $file->filename ?? ('File-' . ($index + 1)); ?></h5>
                                                        <h6 class="file-size text-muted m-0"><?php echo $file->size ?? '-' ?></h6>
                                                    </div>
                                                    <div class="">
                                                        <button type="button" class="btn btn-light btn-sm pb-0 border remove-file"><i data-feather="trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <div class="border rounded bg-light text-center p-3 mt-3">
                                        <button type="button" class="btn btn-secondary upload-product-file">Upload your files</button>
                                    </div>
                                </div>

                                <div class="p-3">
                                    <div class="border rounded bg-light text-center p-5 no-product-file-box" <?php echo count($product->files) ? 'style="display:none"' : '' ?>>
                                        <i data-feather="hard-drive" width="42" height="42"></i>
                                        <h3 class="text-muted">Upload or select files for this product</h3>
                                        <button type="button" class="btn btn-light border shadow-sm upload-product-file">Upload files</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="form-row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="base_price" class="text-muted my-2 d-block"><strong>Base price</strong></label>
                                            <input type="text" class="form-control" id="base_price" name="base_price" value="<?php echo $product->base_price; ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="sale_price" class="text-muted my-2 d-block"><strong>Sales price</strong></label>
                                            <input type="text" class="form-control" id="sale_price" name="sale_price" value="<?php echo $product->sale_price; ?>">
                                        </div>
                                    </div>
                                </div>


                                <div class="smartpay-variations">
                                    <!-- Add variations -->
                                    <div class="border rounded bg-light text-center p-5 no-variations-box" <?php echo count($product->variations) ? 'style="display:none"' : '' ?>>
                                        <i data-feather="layers" width="42" height="42"></i>
                                        <h3>Offer variations of this product</h3>
                                        <p class="text-muted">Sweeten the deal for your customers with different options for format, version, etc</p>
                                        <button type="button" class="btn btn-light border shadow-sm add-variation">Add Variations</button>
                                    </div>

                                    <div class="card p-0 variations-secion" <?php echo !count($product->variations) ? 'style="display:none"' : '' ?>>
                                        <input type="hidden" name="has_variations" id="has_variations" value="<?php echo count($product->variations) ? 1 : 0 ?>">
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
                                            <?php foreach ($product->variations as $index => $variation) : ?>
                                            <?php $variation_id = $variation['id'] ?? 'variation_' + $index + 1; ?>
                                            <div class="variation-option" data-variation-id="<?php echo $variation_id; ?>" data-variation-status="saved">
                                                <div class="variation-option__header p-3">
                                                    <div class="form-row">
                                                        <div class="col-11">
                                                            <div class="form-group m-0">
                                                                <label for="<?php echo 'variations[' . $variation_id . '][name]'; ?>" class="text-muted my-2 d-block"><strong>Option
                                                                        name</strong></label>
                                                                <input type="text" class="form-control" id="<?php echo 'variations[' . $variation_id . '][name]'; ?>" name="<?php echo 'variations[' . $variation_id . '][name]'; ?>" placeholder="Option name" value="<?php echo $variation->title ?? ''; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col d-flex align-items-center">
                                                            <div class="mt-4">
                                                                <button type="button" class="btn btn-light btn-sm border shadow-sm pb-0 ml-2 remove-variation-option"><i data-feather="trash" width="20" height="20"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-row mt-4">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="<?php echo "{$variation_id}_base_price"; ?>" class=" text-muted my-2 d-block"><strong>Base price</strong></label>
                                                                <input type="text" class="form-control" id="<?php echo "{$variation_id}_base_price"; ?>" name=" base_price" value="<?php echo $variation->base_price; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="<?php echo "{$variation_id}_sale_price"; ?>" class="text-muted my-2 d-block"><strong>Sales price</strong></label>
                                                                <input type="text" class="form-control" id="<?php echo "{$variation_id}_sale_price"; ?>" name="sale_price" value="<?php echo $variation->sale_price; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="variation-option-body bg-light p-3">
                                                    <div class="form-group">
                                                        <label for="<?php echo 'variations[' . $variation_id . '][description]'; ?>" class="text-muted my-2 d-block"><strong>Description</strong></label>
                                                        <textarea class="form-control" id="<?php echo 'variations[' . $variation_id . '][description]'; ?>" name="<?php echo 'variations[' . $variation_id . '][description]'; ?>" rows="3"><?php echo $variation ? $variation['description'] : ''; ?></textarea>
                                                        <small class="form-text text-muted">Do not write HTML code here.</small>
                                                    </div>

                                                    <!-- Files -->
                                                    <label class="text-muted my-2 d-block"><strong>Files</strong></label>
                                                    <div class="form-group no-variation-file-box" <?php echo count($variation->files) ? 'style="display:none"' : '' ?>>
                                                        <div class="border rounded text-center p-5">
                                                            <i data-feather="package" width="42" height="42"></i>
                                                            <h3 class="text-muted">Associate files with this variant</h3>
                                                            <button type="button" class="btn btn-light border shadow-sm select-variation-files">
                                                                Select files</button>
                                                        </div>
                                                    </div>
                                                    <div class="variation-files-secion" <?php echo !count($variation->files) ? 'style="display:none"' : '' ?>>
                                                        <input type="hidden" name="<?php echo 'variations[' . $variation_id . '][has_files]'; ?>" id="<?php echo 'variations[' . $variation_id . '][has_files]'; ?>" value="<?php echo count($variation->files) ? 1 : 0 ?>">
                                                        <ul class="list-group variation-files">

                                                            <!-- Variation files -->
                                                            <?php foreach ($variation->files as $file) : ?>
                                                            <li class="list-group-item m-0 d-flex justify-content-between files-item">
                                                                <div class="custom-checkbox custom-checkbox-round">
                                                                    <input type="checkbox" class="custom-control-input variation-file" id="<?php echo 'variations[' . $variation_id . '][files][' . $file->id . ']'; ?>" name="<?php echo 'variations[' . $variation_id . '][files][' . $file->id . ']'; ?>" value="1" checked>
                                                                    <label class="custom-control-label" for="<?php echo 'variations[' . $variation_id . '][files][' . $file->id . ']'; ?>"><?php echo $file->filename ?></label>
                                                                </div>
                                                            </li>
                                                            <?php endforeach; ?>

                                                        </ul>

                                                        <div class="border rounded bg-light text-center p-3 mt-3">
                                                            <button type="button" class="btn btn-sm btn-light border upload-product-file">Upload more
                                                                file</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="card-footer bg-white p3 mt-3">
                                            <button class="btn btn-secondary add-variation-option">Add option</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <button type="submit" class="btn btn-primary px-3"><?php _e('Publish', 'smartpay'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>