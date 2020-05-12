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