<?php
$files = $product->get_files() ?? [];
?>

<div class="border rounded bg-light text-center p-5 no-product-file-box"
    <?php echo count($files) ? 'style="display:none"': '' ?>>
    <i data-feather="hard-drive" width="42" height="42"></i>
    <h3 class="text-muted">Upload or select files for this product</h3>
    <button type="button" class="btn btn-light border shadow-sm upload-product-file">Upload files</button>
</div>
<div class="product-files-secion" <?php echo !count($files) ? 'style="display:none"': '' ?>>
    <ul class="list-group product-files" id="product-files">
        <?php foreach ($files as $index => $file) : ?>
        <?php $id = $file['id'] ?? $index + 1; ?>
        <li class="list-group-item list-group-item-action mb-0 files-item" id="file-<?php echo $id ?>"
            data-file-id="<?php echo $id ?>">
            <input type="hidden" class="form-control file-id" name="<?php echo 'files[' . $id . '][id]'; ?>"
                value="<?php echo $id ?>">
            <input type="hidden" class="form-control file-icon" name="<?php echo 'files[' . $id . '][icon]'; ?>"
                value="<?php echo $file['icon'] ?? '' ?>">
            <input type="hidden" class="form-control file-filename" name="<?php echo 'files[' . $id . '][filename]'; ?>"
                value="<?php echo $file['filename'] ?? '' ?>">
            <input type="hidden" class="form-control file-mime" name="<?php echo 'files[' . $id . '][mime]'; ?>"
                value="<?php echo $file['mime'] ?? '' ?>">
            <input type="hidden" class="form-control file-size" name="<?php echo 'files[' . $id . '][size]'; ?>"
                value="<?php echo $file['size'] ?? '' ?>">
            <input type="hidden" class="form-control file-url" name="<?php echo 'files[' . $id . '][url]'; ?>"
                value="<?php echo $file['url'] ?? '' ?>">

            <div class="d-flex">
                <div class="file-type">
                    <img src="<?php echo $file['icon'] ?? '#' ?>" alt="<?php echo $file['mime'] ?? '-' ?>" width="28"
                        height="28" />
                </div>
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column ml-3">
                        <h5 class="file-name m-0"><?php echo $file['filename'] ?? 'File-' . $index + 1 ?></h5>
                        <h6 class="file-size text-muted m-0"><?php echo $file['size'] ?? '-' ?></h6>
                    </div>
                    <div class="">
                        <button type="button" class="btn btn-light btn-sm pb-0 border remove-file"><i
                                data-feather="trash"></i></button>
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