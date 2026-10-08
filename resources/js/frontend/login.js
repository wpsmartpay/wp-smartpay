(function($) {
    'use strict';

    // ========================================
    // FORM VALIDATION
    // ========================================

    const FormValidation = {
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
    // LOGIN FORM
    // ========================================

    const LoginForm = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('#smartpay-login-form').on('submit', this.handleSubmit.bind(this));

            // Real-time validation
            $(document).on('input', '#smartpay-login-form input', this.clearFieldError);
        },

        handleSubmit: function(e) {
            e.preventDefault();

            const $form = $(e.target);
            const $button = $form.find('button[type="submit"]');
            const originalText = $button.find('.btn-text').text();

            FormValidation.clearErrors($form);
            $form.find('.form-message').html('');

            // Basic validation
            const username = $form.find('[name="username"]').val().trim();
            const password = $form.find('[name="password"]').val();

            if (!username) {
                FormValidation.showError($form.find('[name="username"]'), 'Username or email is required');
                return;
            }

            if (!password) {
                FormValidation.showError($form.find('[name="password"]'), 'Password is required');
                return;
            }

            $button.prop('disabled', true);
            $button.find('.btn-text').text('Signing in...');

            const data = $form.serialize() + '&action=smartpay_user_login&nonce=' + (window.smartpayData?.nonce || '');

            $.post(window.smartpayData?.ajaxUrl || '/wp-admin/admin-ajax.php', data)
                .done(response => this.handleResponse(response, $form, $button, originalText))
                .fail(() => this.handleError($form, $button, originalText));
        },

        handleResponse: function(response, $form, $button, originalText) {
            const $message = $form.find('.form-message');

            if (response.success) {
                $message.html('<div class="alert alert-success">' + response.data.message + '</div>');

                // Redirect
                setTimeout(() => {
                    window.location.href = response.data.redirect || '/';
                }, 1000);
            } else {
                $message.html('<div class="alert alert-error">' + (response.data.message || 'Invalid credentials') + '</div>');
                $button.prop('disabled', false);
                $button.find('.btn-text').text(originalText);
            }
        },

        handleError: function($form, $button, originalText) {
            const $message = $form.find('.form-message');
            $message.html('<div class="alert alert-error">An error occurred. Please try again.</div>');

            $button.prop('disabled', false);
            $button.find('.btn-text').text(originalText);
        },

        clearFieldError: function() {
            const $field = $(this);
            if ($field.hasClass('error') && $field.val().trim() !== '') {
                $field.removeClass('error');
                $field.siblings('.error-message').text('').hide();
            }
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

        if ($('.smartpay-login-page').length) {
            LoginForm.init();
        }

        // Password toggle is universal
        PasswordToggle.init();
    });

})(jQuery);
