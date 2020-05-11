// SmartPayMediaSelector
(function ($) {
    window.SmartPayMediaSelector = function (options) {
        /** Instance to self. **/
        var self = this;

        /** Configuration options. **/
        self.options = $.extend({}, {
            /** Title of the media selection modal window. **/
            title: 'Media',

            /** Set to true to activate multiple selection. **/
            multiple: false,

            /** Restrict items by type. Pick from: '', 'image', 'audio', 'video'. **/
            type: '',

            /** Text of the modal submit button. **/
            button: 'Select',

			/**
			 * Modal window close callback function. Fired when the modal
			 * window is closed without confirming the selection.
			 */
            close: function () { },

			/**
			 * Modal window select callback function. Fired when the modal
			 * window is closed confirming the selection. The passed
			 * argument is an array containing the selected image(s).
			 *
			 * @param {Array} selection Array containing the selected image(s).
			 */
            select: function (selection) { }
        }, options);

        self.frame = null;

		/**
		 * Open the WordPress Media Manager modal window. If an array of IDs is
		 * specified, the modal window will pre-select the relative attachments.
		 * If IDs are specified, the modal window will open itself on the 'Browse'
		 * tab instead of the 'Upload' tab.
		 *
		 * @param  {Array} ids An array of attachment IDs.
		 */
        self.open = function (ids) {
            self.frame = wp.media({
                title: self.options.title,
                button: { text: self.options.button },
                library: {
                    type: self.options.type
                },
                multiple: self.options.multiple
            });

            self.frame.on('open', function () {
                var selection = self.frame.state().get('selection');
                selection.reset();

                _.each(ids, function (id) {
                    var attachment = wp.media.attachment(id);

                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            });

            self.frame.on('select', function () {
                var selection = self.frame.state().get('selection'),
                    result = self.options.multiple ? selection.toJSON() : selection.first().toJSON();

                self.options.select(result);
                delete self;
            });

            self.frame.on('close', function () {
                self.options.close();
                delete self;
            });

            self.frame.open();
        };
    }
})(jQuery);

// Select media
jQuery(function ($) {
    $(document.body).on("click", "#select_smartpay_product_file", (e) => {
        e.preventDefault();

        var media = new SmartPayMediaSelector({
            multiple: true,
            select: function (selected_files) {

                if (!selected_files.length) {
                    return;
                }

                console.log(selected_files);
                $smartpay_product_files = $('#smartpay_product_files');

                selected_files.forEach(file => {

                    let file_icon = file.sizes ? file.sizes.thumbnail.url : file.icon;
                    let id = file.id;
                    // let arr = { id: id, icon: file_icon, filename: file.filename, mime: file.mime };

                    let listItem = `<li class="list-group-item list-group-item-action mb-0 files-item" id="file-${id}">
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

                    $smartpay_product_files.append(listItem);
                });
                feather.replace()

            }
        });

        media.open();
    });

    $(document.body).on("click", ".remove-file", (e) => {
        $(e.target).parents(".files-item").remove();
    });
});
