jQuery(document).ready(function ($) {
	/**
	 * Settings screen JS
	 */
	var SmartPay_Settings = {
		init: function () {
			this.general();
			this.taxes();
			this.emails();
			this.misc();
		},

		general: function () {
			var edd_color_picker = $('.edd-color-picker');

			if (edd_color_picker.length) {
				edd_color_picker.wpColorPicker();
			}

			// Settings Upload field JS
			if (typeof wp === 'undefined' || '1' !== edd_vars.new_media_ui) {
				//Old Thickbox uploader
				var smartpay_settings_upload_button = $(
					'.smartpay_settings_upload_button'
				);
				if (smartpay_settings_upload_button.length > 0) {
					window.formfield = '';

					$(document.body).on(
						'click',
						smartpay_settings_upload_button,
						function (e) {
							e.preventDefault();
							window.formfield = $(this).parent().prev();
							window.tbframe_interval = setInterval(function () {
								jQuery('#TB_iframeContent')
									.contents()
									.find('.savesend .button')
									.val(edd_vars.use_this_file)
									.end()
									.find('#insert-gallery, .wp-post-thumbnail')
									.hide();
							}, 2000);
							tb_show(
								edd_vars.add_new_download,
								'media-upload.php?TB_iframe=true'
							);
						}
					);

					window.edd_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr(
								'href'
							);
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.edd_send_to_editor(html);
						}
						window.send_to_editor = window.edd_send_to_editor;
						window.formfield = '';
						window.imagefield = false;
					};
				}
			} else {
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$(document.body).on(
					'click',
					'.smartpay_settings_upload_button',
					function (e) {
						e.preventDefault();

						var button = $(this);

						window.formfield = $(this).parent().prev();

						// If the media frame already exists, reopen it.
						if (file_frame) {
							//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.file_frame = wp.media({
							frame: 'post',
							state: 'insert',
							title: button.data('uploader_title'),
							button: {
								text: button.data('uploader_button_text'),
							},
							multiple: false,
						});

						file_frame.on('menu:render:default', function (view) {
							// Store our views in an object.
							var views = {};

							// Unset default menu items
							view.unset('library-separator');
							view.unset('gallery');
							view.unset('featured-image');
							view.unset('embed');

							// Initialize the views in our view object.
							view.set(views);
						});

						// When an image is selected, run a callback.
						file_frame.on('insert', function () {
							var selection = file_frame.state().get('selection');
							selection.each(function (attachment, index) {
								attachment = attachment.toJSON();
								window.formfield.val(attachment.url);
							});
						});

						// Finally, open the modal
						file_frame.open();
					}
				);

				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';
			}
		},

		taxes: function () {
			var no_states = $('select.edd-no-states');
			if (no_states.length) {
				no_states.closest('tr').addClass('hidden');
			}

			// Update base state field based on selected base country
			$('select[name="edd_settings[base_country]"]').change(function () {
				var $this = $(this),
					$tr = $this.closest('tr');
				var data = {
					action: 'edd_get_shop_states',
					country: $this.val(),
					nonce: $this.data('nonce'),
					field_name: 'edd_settings[base_state]',
				};
				$.post(ajaxurl, data, function (response) {
					if ('nostates' == response) {
						$tr.next().addClass('hidden');
					} else {
						$tr.next().removeClass('hidden');
						$tr.next().find('select').replaceWith(response);
					}
				});

				return false;
			});

			// Update tax rate state field based on selected rate country
			$(document.body).on(
				'change',
				'#edd_tax_rates select.edd-tax-country',
				function () {
					var $this = $(this);
					var data = {
						action: 'edd_get_shop_states',
						country: $this.val(),
						nonce: $this.data('nonce'),
						field_name: $this
							.attr('name')
							.replace('country', 'state'),
					};
					$.post(ajaxurl, data, function (response) {
						if ('nostates' == response) {
							var text_field =
								'<input type="text" name="' +
								data.field_name +
								'" value=""/>';
							$this
								.parent()
								.next()
								.find('select')
								.replaceWith(text_field);
						} else {
							$this.parent().next().find('input,select').show();
							$this
								.parent()
								.next()
								.find('input,select')
								.replaceWith(response);
						}
					});

					return false;
				}
			);

			// Insert new tax rate row
			$('#edd_add_tax_rate').on('click', function () {
				var row = $('#edd_tax_rates tr:last');
				var clone = row.clone();
				var count = row.parent().find('tr').length;
				clone.find('td input').not(':input[type=checkbox]').val('');
				clone.find('td [type="checkbox"]').attr('checked', false);
				clone.find('input, select').each(function () {
					var name = $(this).attr('name');
					name = name.replace(
						/\[(\d+)\]/,
						'[' + parseInt(count) + ']'
					);
					$(this).attr('name', name).attr('id', name);
				});
				clone.find('label').each(function () {
					var name = $(this).attr('for');
					name = name.replace(
						/\[(\d+)\]/,
						'[' + parseInt(count) + ']'
					);
					$(this).attr('for', name);
				});
				clone.insertAfter(row);
				return false;
			});

			// Remove tax row
			$(document.body).on(
				'click',
				'#edd_tax_rates .edd_remove_tax_rate',
				function () {
					if (confirm(edd_vars.delete_tax_rate)) {
						var tax_rates = $('#edd_tax_rates tr:visible');
						var count = tax_rates.length;

						if (count === 2) {
							$('#edd_tax_rates select').val('');
							$('#edd_tax_rates input[type="text"]').val('');
							$('#edd_tax_rates input[type="number"]').val('');
							$('#edd_tax_rates input[type="checkbox"]').attr(
								'checked',
								false
							);
						} else {
							$(this).closest('tr').remove();
						}

						/* re-index after deleting */
						$('#edd_tax_rates tr').each(function (rowIndex) {
							$(this)
								.children()
								.find('input, select')
								.each(function () {
									var name = $(this).attr('name');
									name = name.replace(
										/\[(\d+)\]/,
										'[' + (rowIndex - 1) + ']'
									);
									$(this).attr('name', name).attr('id', name);
								});
						});
					}
					return false;
				}
			);
		},

		emails: function () {
			// Show the email template previews
			var email_preview_wrap = $('#email-preview-wrap');
			if (email_preview_wrap.length) {
				var emailPreview = $('#email-preview');
				email_preview_wrap.colorbox({
					inline: true,
					href: emailPreview,
					width: '80%',
					height: 'auto',
				});
			}

			$('#edd-sendwp-connect').on('click', function (e) {
				e.preventDefault();
				$(this).html(
					edd_vars.wait + ' <span class="edd-loading"></span>'
				);
				document.body.style.cursor = 'wait';
				easy_digital_downloads_sendwp_remote_install();
			});

			$('#edd-sendwp-disconnect').on('click', function (e) {
				e.preventDefault();
				$(this).html(
					edd_vars.wait + ' <span class="edd-loading dark"></span>'
				);
				document.body.style.cursor = 'wait';
				easy_digital_downloads_sendwp_disconnect();
			});

			$('#edd-jilt-connect').on('click', function (e) {
				e.preventDefault();
				$(this).html(
					edd_vars.wait + ' <span class="edd-loading"></span>'
				);
				document.body.style.cursor = 'wait';
				easy_digital_downloads_jilt_remote_install();
			});

			$('#edd-jilt-disconnect').on('click', function (e) {
				e.preventDefault();
				$(this).html(
					edd_vars.wait + ' <span class="edd-loading dark"></span>'
				);
				document.body.style.cursor = 'wait';
				easy_digital_downloads_jilt_disconnect();
			});
		},

		misc: function () {
			var downloadMethod = $(
				'select[name="edd_settings[download_method]"]'
			);
			var symlink = downloadMethod.parent().parent().next();

			// Hide Symlink option if Download Method is set to Direct
			if (downloadMethod.val() == 'direct') {
				symlink.hide();
				symlink.find('input').prop('checked', false);
			}
			// Toggle download method option
			downloadMethod.on('change', function () {
				if ($(this).val() == 'direct') {
					symlink.hide();
					symlink.find('input').prop('checked', false);
				} else {
					symlink.show();
				}
			});
		},
	};
	// TODO:: Fix ulpad button
	// SmartPay_Settings.init();
});
