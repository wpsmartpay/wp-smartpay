(function($) {
    'use strict';

    // ========================================
    // PROFILE NAVIGATION
    // ========================================

    const ProfileNavigation = {
        init: function() {
            this.bindEvents();
            this.handleHashChange();
        },

        bindEvents: function() {
            $('.profile-tabs .tab-item').on('click', this.handleNavClick.bind(this));
            $(window).on('hashchange', this.handleHashChange.bind(this));
        },

        handleNavClick: function(e) {
            e.preventDefault();
            const $link = $(e.currentTarget);
            const targetId = $link.attr('href');

            // Update URL hash
			if (history.pushState) {
				history.pushState(null, null, targetId);
			} else {
				const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
			}

            // Update navigation
            this.updateNav(targetId);
        },

        handleHashChange: function() {
            const hash = window.location.hash || '#personal-info';
            this.updateNav(hash);
        },

        updateNav: function(targetId) {
            // Update active nav item
            $('.profile-tabs .tab-item').removeClass('active');
            $(`.profile-tabs .tab-item[href="${targetId}"]`).addClass('active');

            // Show corresponding form section
            $('.form-section').removeClass('active');
            $(targetId).addClass('active');
        }
    };

    // ========================================
    // FORM VALIDATION
    // ========================================

    const FormValidation = {
        validators: {
            required: value => value.trim() !== '',
            email: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            phone: value => !value || /^[\d\s\-\+\(\)]+$/.test(value),
            minLength: (value, length) => value.length >= length,
            match: (value, target) => value === target
        },

        rules: {
            'personal-info': {
                first_name: ['required'],
                last_name: ['required'],
                email: ['required', 'email'],
                phone: [{ rule: 'phone' }]
            },
            'address': {
                address_line1: ['required'],
                city: ['required'],
                state: ['required'],
                postal_code: ['required'],
                country: ['required']
            },
            'security': {
                current_password: ['required'],
                new_password: ['required', { rule: 'minLength', value: 8 }],
                confirm_new_password: [{ rule: 'match', target: 'new_password' }]
            }
        },

        validate: function($form, formType) {
            const rules = this.rules[formType];
            if (!rules) return true;

            let valid = true;

            Object.entries(rules).forEach(([field, ruleSet]) => {
                const $field = $form.find(`[name="${field}"]`);
                if (!$field.length) return;

                const value = $field.val() || '';
                let fieldValid = true;

                ruleSet.forEach(rule => {
                    if (!fieldValid) return;

                    if (typeof rule === 'string') {
                        if (!this.validators[rule](value)) {
                            this.showError($field, this.getErrorMessage(field, rule));
                            fieldValid = false;
                            valid = false;
                        }
                    }

                    if (typeof rule === 'object') {
                        if (rule.rule === 'minLength' && !this.validators.minLength(value, rule.value)) {
                            this.showError($field, `Minimum ${rule.value} characters required`);
                            fieldValid = false;
                            valid = false;
                        }

                        if (rule.rule === 'match') {
                            const targetVal = $form.find(`[name="${rule.target}"]`).val();
                            if (!this.validators.match(value, targetVal)) {
                                this.showError($field, 'Passwords do not match');
                                fieldValid = false;
                                valid = false;
                            }
                        }

                        if (rule.rule === 'phone' && value && !this.validators.phone(value)) {
                            this.showError($field, 'Please enter a valid phone number');
                            fieldValid = false;
                            valid = false;
                        }
                    }
                });
            });

            return valid;
        },

        showError: function($field, message) {
            $field.addClass('error');
            const $errorMsg = $field.siblings('.error-message');
            if ($errorMsg.length) {
                $errorMsg.text(message).show();
            } else {
                $field.after('<span class="error-message" style="display: block;">' + message + '</span>');
            }
        },

        clearErrors: function($form) {
            $form.find('.error').removeClass('error');
            $form.find('.error-message').text('').hide();
        },

        getErrorMessage: function(field, rule) {
            const messages = {
                first_name: 'First name is required',
                last_name: 'Last name is required',
                email: 'Valid email address is required',
                phone: 'Please enter a valid phone number',
                address_line1: 'Street address is required',
                city: 'City is required',
                state: 'State/Province is required',
                postal_code: 'Postal code is required',
                country: 'Country is required',
                current_password: 'Current password is required',
                new_password: 'New password is required',
                confirm_new_password: 'Please confirm your password'
            };

            return messages[field] || 'This field is required';
        }
    };

    // ========================================
    // PROFILE FORMS
    // ========================================

    const ProfileForms = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('#personal-info-form').on('submit', this.handlePersonalInfoSubmit.bind(this));
            $('#address-form').on('submit', this.handleAddressSubmit.bind(this));
            $('#security-form').on('submit', this.handleSecuritySubmit.bind(this));
            $('#preferences-form').on('submit', this.handlePreferencesSubmit.bind(this));

            // Real-time validation
            $(document).on('input change', '.profile-form input, .profile-form select', this.clearFieldError);
            $(document).on('blur', '[name="email"]', this.validateEmail);
            $(document).on('input', '[name="new_password"]', this.handlePasswordInput);
            $(document).on('input', '[name="confirm_new_password"]', this.validatePasswordMatch);

            // Cancel buttons
            $('.profile-form .btn-secondary').on('click', this.handleCancel.bind(this));
        },

        handlePersonalInfoSubmit: function(e) {
            e.preventDefault();
            const $form = $(e.target);

            FormValidation.clearErrors($form);
            $form.find('.form-message').html('');

            if (!FormValidation.validate($form, 'personal-info')) {
                return;
            }

            this.submitForm($form, 'smartpay_update_personal_info');
        },

        handleAddressSubmit: function(e) {
            e.preventDefault();
            const $form = $(e.target);

            FormValidation.clearErrors($form);
            $form.find('.form-message').html('');

            if (!FormValidation.validate($form, 'address')) {
                return;
            }

            this.submitForm($form, 'smartpay_update_address');
        },

        handleSecuritySubmit: function(e) {
            e.preventDefault();
            const $form = $(e.target);

            FormValidation.clearErrors($form);
            $form.find('.form-message').html('');

            if (!FormValidation.validate($form, 'security')) {
                return;
            }

            this.submitForm($form, 'smartpay_update_password', function() {
				setTimeout(() => {
					location.reload();
				}, 1500);
			});
        },

        handlePreferencesSubmit: function(e) {
            e.preventDefault();
            const $form = $(e.target);

            this.submitForm($form, 'smartpay_update_preferences');
        },

        submitForm: function($form, action, responseSuccessAction) {
            const $button = $form.find('button[type="submit"]');
            const originalText = $button.find('.btn-text').text();

            $button.prop('disabled', true);
            $button.find('.btn-text').text('Saving...');

            const data = $form.serialize() + '&action=' + action + '&nonce=' + (window.smartpayData?.nonce || '');

            $.post(window.smartpayData?.ajaxUrl || '/wp-admin/admin-ajax.php', data)
                .done(response => this.handleResponse(response, $form, $button, originalText, responseSuccessAction))
                .fail(() => this.handleError($form, $button, originalText));
        },

        handleResponse: function(response, $form, $button, originalText, responseSuccessAction) {
            const $message = $form.find('.form-message');

            if (response.success) {
                $message.html('<div class="alert alert-success">' + response.data.message + '</div>');

                // Clear form if it's password form
                if ($form.attr('id') === 'security-form') {
                    $form[0].reset();
                }

				if (responseSuccessAction) {
					responseSuccessAction()
				}

                setTimeout(() => {
                    $message.fadeOut(300, function() {
                        $(this).html('').show();
                    });
                }, 3000);
            } else {
				this.handleErrors(response.data, $form, $message);
            }

            $button.prop('disabled', false);
            $button.find('.btn-text').text(originalText);
        },

        handleErrors: function(data, $form, $message) {
            if (data.errors) {
                $.each(data.errors, (field, error) => {
                    const $field = $form.find(`[name="${field}"]`);
                    if ($field.length) {
                        FormValidation.showError($field, error);
                    }
                });
            }

            if (data.message) {
                $message.html('<div class="alert alert-error">' + data.message + '</div>');
            }
        },

        handleError: function($form, $button, originalText) {
            const $message = $form.find('.form-message');
            $message.html('<div class="alert alert-error">An error occurred. Please try again.</div>');

            $button.prop('disabled', false);
            $button.find('.btn-text').text(originalText);
        },

        handleCancel: function(e) {
            e.preventDefault();
            const $form = $(e.target).closest('form');
            $form[0].reset();
            FormValidation.clearErrors($form);
            $form.find('.form-message').html('');
        },

        clearFieldError: function() {
            const $field = $(this);
            if ($field.hasClass('error') && $field.val().trim() !== '') {
                $field.removeClass('error');
                $field.siblings('.error-message').text('').hide();
            }
        },

        validateEmail: function(e) {
            const $field = $(e.target);
            const value = $field.val().trim();

            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                FormValidation.showError($field, 'Valid email address is required');
            }
        },

        handlePasswordInput: function(e) {
            const $field = $(e.target);
            const value = $field.val();

            // Update password strength
            PasswordStrength.update($field, value);

            // Re-validate confirm password if it has value
            const $confirm = $field.closest('form').find('[name="confirm_new_password"]');
            if ($confirm.val()) {
                $confirm.trigger('input');
            }
        },

        validatePasswordMatch: function(e) {
            const $confirm = $(e.target);
            const $form = $confirm.closest('form');
            const $password = $form.find('[name="new_password"]');

            if (!$password.val()) return;

            const passwordVal = $password.val();
            const confirmVal = $confirm.val();

            if (confirmVal && passwordVal !== confirmVal) {
                FormValidation.showError($confirm, 'Passwords do not match');
            } else if (confirmVal) {
                $confirm.removeClass('error');
                $confirm.siblings('.error-message').text('').hide();
            }
        }
    };

    // ========================================
    // PASSWORD STRENGTH
    // ========================================

    const PasswordStrength = {
        update: function($field, password) {
            const $wrapper = $field.closest('.password-input-wrapper');
            let $strength = $wrapper.siblings('.password-strength');

            if (!$strength.length) {
                $strength = $('<div class="password-strength"></div>');
                $wrapper.after($strength);
            }

            if (!password) {
                $strength.removeClass('show weak medium strong');
                return;
            }

            $strength.addClass('show');

            // Calculate strength
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;

            $strength.removeClass('weak medium strong');
            if (strength <= 2) {
                $strength.addClass('weak');
            } else if (strength === 3) {
                $strength.addClass('medium');
            } else {
                $strength.addClass('strong');
            }
        }
    };

    // ========================================
    // PASSWORD TOGGLE
    // ========================================

    const PasswordToggle = {
        init: function() {
            $(document).on('click', '.password-toggle', this.toggle);
        },

        toggle: function(e) {
            e.preventDefault();
            const $button = $(this);
            const targetName = $button.data('target');
            const $input = $(`[name="${targetName}"]`);

            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $button.attr('aria-label', 'Hide password');

				// Change icon to eye-off
				$button.find('.eye-icon').attr('viewBox', '0 0 24 24').html(`
					<path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="m2 2 20 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				`);
            } else {
                $input.attr('type', 'password');
                $button.attr('aria-label', 'Show password');

				// Change icon back to eye
				$button.find('.eye-icon').attr('viewBox', '0 0 20 20').html(`
					<path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
					<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
				`);
            }
        }
    };


	// ========================================
	// AVATAR UPLOAD
	// ========================================

	const AvatarUpload = {
		selectedFile: null,

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			// Choose photo button
			$('#choose-avatar-btn').on('click', () => {
				$('#avatar-upload').click();
			});

			// File selection
			$('#avatar-upload').on('change', this.handleFileSelect.bind(this));

			// Save photo button
			$('#save-avatar-btn').on('click', this.handleSave.bind(this));

			// Cancel button
			$('#cancel-avatar-btn').on('click', this.handleCancel.bind(this));

			// Remove avatar button
			$('#remove-avatar-btn').on('click', this.handleRemove.bind(this));
		},

		handleFileSelect: function(e) {
			const file = e.target.files[0];
			if (!file) return;

			// Clear any previous messages
			this.hideMessage();

			const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
			if (!validTypes.includes(file.type)) {
				this.showMessage('Please select a valid image file (JPG, PNG, or GIF)', 'error');
				this.resetFileInput();
				return;
			}

			const maxSize = 2 * 1024 * 1024; // 2MB in bytes
			if (file.size > maxSize) {
				this.showMessage('File size must be less than 2MB. Please choose a smaller image.', 'error');
				this.resetFileInput();
				return;
			}

			// Store the file for later upload
			this.selectedFile = file;

			this.showPreview(file);
		},

		showPreview: function(file) {
			const reader = new FileReader();

			reader.onload = (e) => {
				// Update preview image
				$('#avatar-preview').attr('src', e.target.result);

				// Show preview container
				$('#avatar-preview-wrapper').fadeIn(300);

				// Update section styling
				$('.avatar-upload-section').addClass('has-preview');

				// Show save and cancel buttons, hide choose button
				$('#choose-avatar-btn').fadeOut(200, function() {
					$('#save-avatar-btn, #cancel-avatar-btn').fadeIn(200);
				});

				this.showMessage('Preview ready. Click "Save Photo" to upload or "Cancel" to choose another.', 'info');
			};

			reader.onerror = () => {
				this.showMessage('Failed to load image preview. Please try again.', 'error');
				this.resetFileInput();
			};

			reader.readAsDataURL(file);
		},

		handleSave: function() {
			if (!this.selectedFile) {
				this.showMessage('No file selected', 'error');
				return;
			}

			this.showProgress();

			// Hide buttons during upload
			$('#save-avatar-btn, #cancel-avatar-btn').prop('disabled', true);

			this.uploadAvatar(this.selectedFile);
		},

		uploadAvatar: function(file) {
			const formData = new FormData();
			formData.append('avatar', file);
			formData.append('action', 'smartpay_upload_avatar');
			formData.append('nonce', window.smartpayData?.nonce || '');

			$.ajax({
				url: window.smartpayData?.ajaxUrl || '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				xhr: function() {
					const xhr = new window.XMLHttpRequest();
					// Upload progress
					xhr.upload.addEventListener('progress', function(e) {
						if (e.lengthComputable) {
							const percentComplete = (e.loaded / e.total) * 100;
							$('.progress-fill').css('width', percentComplete + '%');
						}
					}, false);
					return xhr;
				},
				success: (response) => {
					this.hideProgress();

					if (response.success) {
						// Update current avatar with new image
						const newAvatarUrl = response.data.avatar_url || $('#avatar-preview').attr('src');

						$('.avatar-img, .sidebar-avatar').each(function() {
							const $img = $(this);
							setTimeout(() => {
								$img.attr('src', newAvatarUrl);
								$img.attr('srcset', newAvatarUrl);
							}, 10);
						});

						this.showMessage('Profile picture updated successfully!', 'success');

						// Reset after successful upload
						setTimeout(() => {
							this.resetUpload();
							this.hideMessage();
						}, 2000);
					} else {
						this.showMessage(response.data?.message || 'Failed to upload avatar. Please try again.', 'error');
						$('#save-avatar-btn, #cancel-avatar-btn').prop('disabled', false);
					}
				},
				error: () => {
					this.hideProgress();
					this.showMessage('An error occurred while uploading. Please check your connection and try again.', 'error');
					$('#save-avatar-btn, #cancel-avatar-btn').prop('disabled', false);
				}
			});
		},

		handleCancel: function() {
			if (this.selectedFile) {
				if (!confirm('Discard the selected photo?')) {
					return;
				}
			}

			this.resetUpload();
			this.showMessage('Upload cancelled. You can choose a different photo.', 'info');

			// Hide message after 3 seconds
			setTimeout(() => {
				this.hideMessage();
			}, 3000);
		},

		handleRemove: function() {
			if (!confirm('Are you sure you want to remove your profile picture? Your account will use the default avatar.')) {
				return;
			}

			this.showProgress('Removing...');

			// Disable buttons
			$('#remove-avatar-btn, #choose-avatar-btn').prop('disabled', true);

			$.post(window.smartpayData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
				action: 'smartpay_remove_avatar',
				nonce: window.smartpayData?.nonce || ''
			})
			.done((response) => {
				this.hideProgress();

				if (response.success) {
					this.showMessage('Profile picture removed successfully.', 'success');

					// Reload page to show default avatar
					setTimeout(() => {
						location.reload();
					}, 1500);
				} else {
					this.showMessage(response.data?.message || 'Failed to remove avatar.', 'error');
					$('#remove-avatar-btn, #choose-avatar-btn').prop('disabled', false);
				}
			})
			.fail(() => {
				this.hideProgress();
				this.showMessage('An error occurred. Please try again.', 'error');
				$('#remove-avatar-btn, #choose-avatar-btn').prop('disabled', false);
			});
		},

		resetUpload: function() {
			// Clear selected file
			this.selectedFile = null;
			this.resetFileInput();

			// Hide preview
			$('#avatar-preview-wrapper').fadeOut(300);

			// Reset section styling
			$('.avatar-upload-section').removeClass('has-preview');

			// Show choose button, hide save and cancel
			$('#save-avatar-btn, #cancel-avatar-btn').fadeOut(200, function() {
				$('#choose-avatar-btn').fadeIn(200);
			});

			// Re-enable buttons
			$('#save-avatar-btn, #cancel-avatar-btn, #choose-avatar-btn').prop('disabled', false);

			// Reset progress
			$('.progress-fill').css('width', '0%');
		},

		resetFileInput: function() {
			$('#avatar-upload').val('');
		},

		showProgress: function(text = 'Uploading...') {
			$('#avatar-upload-progress').fadeIn(200);
			$('.progress-text').text(text);
			$('.progress-fill').css('width', '0%');
			this.hideMessage();
		},

		hideProgress: function() {
			$('#avatar-upload-progress').fadeOut(200);
		},

		showMessage: function(text, type = 'info') {
			const $message = $('#avatar-message');
			$message
				.removeClass('success error info')
				.addClass(type + ' show')
				.text(text);
		},

		hideMessage: function() {
			$('#avatar-message').removeClass('show');
		}
	};

    // ========================================
    // INITIALIZE ALL COMPONENTS
    // ========================================

    $(document).ready(function() {
        // Initialize smartpayData if not defined
        if (typeof window.smartpayData === 'undefined') {
            window.smartpayData = {
                ajaxUrl: '/wp-admin/admin-ajax.php',
                nonce: '',
                strings: {
                    processing: 'Processing...',
                    error: 'An error occurred. Please try again.'
                }
            };
            console.warn('smartpayData not found, using defaults');
        }

        // Initialize components based on page
        if ($('.smartpay-profile-edit').length) {
            ProfileNavigation.init();
            ProfileForms.init();
			AvatarUpload.init();
        }

        // Password toggle is universal
        PasswordToggle.init();
    });

})(jQuery);
