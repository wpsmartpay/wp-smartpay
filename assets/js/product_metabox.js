jQuery(function ($) {

    /** Files **/

    // Select file
    $(document.body).on('click', '.upload-product-file', (e) => {
        e.preventDefault();

        var media = new SmartPayMediaSelector({
            multiple: true,
            title: 'Select files',
            select: function (selected_files) {

                if (!selected_files.length) {
                    return;
                }

                $productFiles = $('.product-files');

                selected_files.forEach(file => {

                    let file_icon = file.sizes ? file.sizes.thumbnail.url : file.icon;
                    let id = file.id;
                    // let arr = { id: id, icon: file_icon, filename: file.filename, mime: file.mime };

                    listItem = `<li class="list-group-item list-group-item-action mb-0 files-item" id="file-${id}">
                        <input type="hidden" class="form-control" name="files[${id}][id]" value="${id}">
                        <input type="hidden" class="form-control" name="files[${id}][icon]" value="${file_icon}">
                        <input type="hidden" class="form-control" name="files[${id}][filename]" value="${file.filename}">
                        <input type="hidden" class="form-control" name="files[${id}][mime]" value="${file.mime}">
                        <input type="hidden" class="form-control" name="files[${id}][size]" value="${file.filesizeHumanReadable}">
                        <input type="hidden" class="form-control" name="files[${id}][url]" value="${file.url}">
                        <div class="d-flex">
                            <div class="file-type">
                            <img src="${file_icon}" alt="" width="28" height="28"/>
                            </div>
                            <div class="d-flex justify-content-between w-100">
                                <div class="d-flex flex-column ml-3">
                                    <h5 class="file-name m-0">${file.filename}</h5>
                                    <h6 class="file-size text-muted m-0">${file.filesizeHumanReadable}</h6>
                                </div>
                                <div class="">
                                    <button type="button" class="btn btn-light btn-sm pb-0 border remove-file"><i data-feather="trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </li>`;

                    $productFiles.append(listItem);
                });
                feather.replace()

                toggleFileSelectBox()
            }
        });

        media.open();
    });

    // Remove file
    $(document.body).on('click', '.remove-file', (e) => {
        $(e.target).parents('.files-item').remove();
        toggleFileSelectBox();
    });

    // Toggle file select box
    function toggleFileSelectBox() {
        $productFiles = $('.product-files');
        if ($productFiles.children('.files-item').length) {
            $('.no-product-file-box').hide();
            $('.product-files-secion').show();
        } else {
            $('.product-files-secion').hide();
            $('.no-product-file-box').show();
        }
    }

    /** Variations **/

    // Add variation
    $(document.body).on('click', '.add-variation', (e) => {
        e.preventDefault()
        productHasVariation(true)

        if (!$('.variations .variation-option').length) {
            addVariationOption()
        }

    });

    // Remove variation
    $(document.body).on('click', '.remove-variation', (e) => {
        e.preventDefault()
        productHasVariation(false)
    });

    // Add variation option
    $(document.body).on('click', '.add-variation-option', (e) => {
        e.preventDefault()
        addVariationOption()

    });

    // Remove variation option
    $(document.body).on('click', '.remove-variation-option', (e) => {
        e.preventDefault()

        $(e.target).parents('.variation-option').remove()

        if (!$('.variations .variation-option').length) {
            productHasVariation(false)
        } else {
            scrollToLastVariationOption()
        }
    });

    function addVariationOption() {
        variationId = $('.variations .variation-option').length + 1

        $variations = $('.variations')
        option = `<div class="variation-option">
            <div class="variation-option__header p-3">
                <div class="form-row">
                    <div class="col-7">
                        <div class="form-group m-0">
                            <label for="variations[${variationId}][name]"
                                class="text-muted my-2 d-block"><strong>Option
                                    name</strong></label>
                            <input type="text" class="form-control"
                                id="variations[${variationId}][name]"
                                name="variations[${variationId}][name]"
                                placeholder="Option name">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group m-0">
                            <label for="variations[${variationId}][additional_amount]"
                                class="text-muted my-2 d-block"><strong>Additional amount</strong></label>
                            <input type="text"
                                name="variations[${variationId}][additional_amount]"
                                id="variations[${variationId}][additional_amount]"
                                class="form-control" placeholder="1.0">
                        </div>
                    </div>
                    <div class="col d-flex align-items-center">
                        <div class="mt-4">
                            <button type="button" class="btn btn-light btn-sm border shadow-sm pb-0 ml-2 remove-variation-option"><i
                                    data-feather="trash" width="20" height="20"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="variation-option-body bg-light p-3">
                <div class="form-group">
                    <label for="variations[${variationId}][description]"
                        class="text-muted my-2 d-block"><strong>Description</strong></label>
                    <textarea class="form-control"
                        id="variations[${variationId}][description]"
                        name="variations[${variationId}][description]"
                        rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="text-muted my-2 d-block"><strong>Files</strong></label>
                    <div class="border rounded text-center p-5">
                        <i data-feather="package" width="42" height="42"></i>
                        <h3 class="text-muted">Associate files with this variant</h3>
                        <button class="btn btn-light border shadow-sm">Select files</button>
                    </div>
                </div>
            </div>
        </div>`;

        $variations.append(option)

        feather.replace()

        scrollToLastVariationOption()

    }

    function scrollToLastVariationOption() {
        $('html, body').animate({
            scrollTop: eval($variations.children('.variation-option').last().offset().top - 70)
        }, 500);
    }

    function productHasVariation(hasVariation = false) {

        if (hasVariation) {
            $('.variations-secion').show();
            $('#has_variations').val('1');
            $('.no-variations-box').hide();
        } else {
            $('.no-variations-box').show()
            $('#has_variations').val('0')
            $('.variations-secion').hide()
        }
    }
});

