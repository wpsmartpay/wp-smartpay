(function($) {
    'use strict';

    // Validators
    const validators = {
        required: value => value.trim() !== '',
        email: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        phone: value => !value || /^[\d\s\-\+\(\)]+$/.test(value),
        minLength: (value, length) => value.length >= length,
        match: (value, target) => value === target,
        postalCode: value => /^[\w\s\-]+$/.test(value)
    };

    // Full form validation rules (all fields at once)
    const formValidationRules = {
        first_name: ['required'],
        last_name: ['required'],
        email: ['required', 'email'],
        phone: [{ rule: 'phone' }],
        address_line_1: ['required'],
        city: ['required'],
        state: ['required'],
        postal_code: ['required', { rule: 'postalCode' }],
        country: ['required'],
        password: ['required', { rule: 'minLength', value: 8 }],
        confirm_password: [{ rule: 'match', target: 'password' }],
        agree_terms: ['required']
    };

    const SmartPayRegistration = {
        init: function() {
            if (typeof window.smartpayData === 'undefined') {
                window.smartpayData = {
                    ajaxUrl: '/wp-admin/admin-ajax.php',
                    nonce: '',
                    strings: {
                        processing: 'Processing...',
                        error: 'An error occurred. Please try again.'
                    },
                    errors: {}
                };
                console.warn('smartpayData not found, using defaults');
            }

            this.bindEvents();
        },

        bindEvents: function() {
            const $form = $('#smartpay-registration-form');

            $form.on('submit', this.handleSubmit.bind(this));

            $(document).on('input', '[name="confirm_password"]', this.validatePasswordMatch.bind(this));
            $(document).on('input change', '.registration-form input, .registration-form textarea, .registration-form select', this.clearFieldError);
            $(document).on('blur', '[name="email"]', this.validateEmailField.bind(this));
            $(document).on('input', '[name="password"]', this.handlePasswordInput.bind(this));
            $(document).on('blur', '[name="phone"]', this.validatePhoneField.bind(this));

            $(document).on('click', '.password-toggle', this.togglePassword);
            $(document).on('change', '[name="agree_terms"]', this.validateCheckbox.bind(this));
        },

        validateForm: function() {
            const $form = $('#smartpay-registration-form');
            let valid = true;
            const errors = this.getErrorMessages();

            Object.entries(formValidationRules).forEach(([field, ruleSet]) => {
                const $field = $form.find(`[name="${field}"]`);
                if (!$field.length) return;

                const value = $field.is(':checkbox') ? ($field.is(':checked') ? 'checked' : '') : ($field.val() || '');
                let fieldValid = true;

                ruleSet.forEach(rule => {
                    if (!fieldValid) return;

                    if (typeof rule === 'string') {
                        if (rule === 'required') {
                            if ($field.is(':checkbox')) {
                                if (!$field.is(':checked')) {
                                    const errorMsg = 'You must agree to the terms';
                                    this.showFieldError($field.closest('.checkbox-group'), errorMsg);
                                    fieldValid = false;
                                    valid = false;
                                }
                            } else if (!validators[rule](value)) {
                                const errorMsg = errors[field] || errors.required || 'This field is required';
                                this.showFieldError($field, errorMsg);
                                fieldValid = false;
                                valid = false;
                            }
                        } else if (!validators[rule](value)) {
                            const errorMsg = errors[field] || errors[rule] || 'Invalid value';
                            this.showFieldError($field, errorMsg);
                            fieldValid = false;
                            valid = false;
                        }
                    }

                    if (typeof rule === 'object') {
                        if (rule.rule === 'minLength' && value && !validators.minLength(value, rule.value)) {
                            const errorMsg = errors.password_length || `Minimum ${rule.value} characters required`;
                            this.showFieldError($field, errorMsg);
                            fieldValid = false;
                            valid = false;
                        }

                        if (rule.rule === 'match') {
                            const targetVal = $form.find(`[name="${rule.target}"]`).val();
                            if (value && !validators.match(value, targetVal)) {
                                const errorMsg = errors.password_match || 'Passwords do not match';
                                this.showFieldError($field, errorMsg);
                                fieldValid = false;
                                valid = false;
                            }
                        }

                        if (rule.rule === 'phone' && value && !validators.phone(value)) {
                            this.showFieldError($field, 'Please enter a valid phone number');
                            fieldValid = false;
                            valid = false;
                        }

                        if (rule.rule === 'postalCode' && !validators.postalCode(value)) {
                            this.showFieldError($field, 'Please enter a valid postal code');
                            fieldValid = false;
                            valid = false;
                        }
                    }
                });
            });

            return valid;
        },

        handleSubmit: function(e) {
            e.preventDefault();

            const $form = $(e.target);
            const $button = $form.find('button[type="submit"]');

            if (!this.validateForm()) {
                const $firstError = $form.find('.form-control.error').first();
                if ($firstError.length) {
                    $firstError.focus();
                }
                return;
            }

            this.clearErrors($form);
            $form.find('.form-message').html('');

            const originalText = $button.find('.btn-text').text();
            $button.prop('disabled', true);
            $button.find('.btn-text').text(smartpayData.strings.processing);

            this.submitAjax($form, $button, originalText);
        },

        submitAjax: function($form, $button, originalText) {
            const data = $form.serialize() + '&action=smartpay_user_registration&nonce=' + smartpayData.nonce;

            $.post(smartpayData.ajaxUrl, data)
                .done(response => this.handleResponse(response, $form, $button, originalText))
                .fail(() => this.handleAjaxError($form, $button, originalText));
        },

        handleResponse: function(response, $form, $button, originalText) {
            const $message = $form.find('.form-message');

            if (response.success) {
                $message.html(
                    '<div class="alert alert-success">' + response.data.message + '</div>'
                );

                setTimeout(() => {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                this.handleErrors(response.data, $form, $message);
                this.resetButton($button, originalText);
            }
        },

        handleAjaxError: function($form, $button, originalText) {
            const $message = $form.find('.form-message');
            $message.html(
                '<div class="alert alert-error">' + smartpayData.strings.error + '</div>'
            );
            this.resetButton($button, originalText);
        },

        handleErrors: function(data, $form, $message) {
            if (data.errors) {
                $.each(data.errors, (field, error) => {
                    const $field = $form.find(`[name="${field}"]`);
                    if ($field.length) {
                        this.showFieldError($field, error);
                    }
                });
            } else if (data.message) {
                $message.html('<div class="alert alert-error">' + data.message + '</div>');
            }
        },

        showFieldError: function($field, message) {
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

        resetButton: function($button, originalText) {
            $button.prop('disabled', false);
            $button.find('.btn-text').text(originalText || 'Create Account');
        },

        clearFieldError: function() {
            const $field = $(this);
            if ($field.hasClass('error') && $field.val().trim() !== '') {
                $field.removeClass('error');
                $field.siblings('.error-message').text('').hide();
            }
        },

        getErrorMessages: function() {
            const defaults = {
                required: 'This field is required',
                first_name: 'First name is required',
                last_name: 'Last name is required',
                email: 'Valid email address is required',
                phone: 'Please enter a valid phone number',
                address_line_1: 'Street address is required',
                city: 'City is required',
                state: 'State/Province is required',
                postal_code: 'Postal code is required',
                country: 'Country is required',
                password: 'Password is required',
                password_length: 'Password must be at least 8 characters',
                password_match: 'Passwords do not match',
                confirm_password: 'Passwords do not match',
                agree_terms: 'You must agree to the terms and conditions.'
            };

            if (typeof smartpayData !== 'undefined' && smartpayData.errors) {
                return { ...defaults, ...smartpayData.errors };
            }

            return defaults;
        },

        validateEmailField: function(e) {
            const $field = $(e.target);
            const value = $field.val().trim();
            const errors = SmartPayRegistration.getErrorMessages();

            if (value && !validators.email(value)) {
                SmartPayRegistration.showFieldError($field, errors.email);
            } else if (value) {
                $field.removeClass('error');
                $field.siblings('.error-message').text('').hide();
            }
        },

        validatePhoneField: function(e) {
            const $field = $(e.target);
            const value = $field.val().trim();

            if (value && !validators.phone(value)) {
                SmartPayRegistration.showFieldError($field, 'Please enter a valid phone number');
            } else if (value) {
                $field.removeClass('error');
                $field.siblings('.error-message').text('').hide();
            }
        },

        handlePasswordInput: function(e) {
            const $field = $(e.target);
            const $form = $field.closest('form');
            const value = $field.val();
            const errors = SmartPayRegistration.getErrorMessages();

            SmartPayRegistration.updatePasswordStrength($field, value);

            if (value && !validators.minLength(value, 8)) {
                SmartPayRegistration.showFieldError($field, errors.password_length);
            } else if (value) {
                $field.removeClass('error');
                $field.siblings('.error-message').text('').hide();

                const $confirm = $form.find('[name="confirm_password"]');
                if ($confirm.length && $confirm.val()) {
                    $confirm.trigger('input');
                }
            }
        },

        updatePasswordStrength: function($field, password) {
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
        },

        validatePasswordMatch: function(e) {
            const $confirm = $(e.target);
            const $form = $confirm.closest('form');
            const $password = $form.find('[name="password"]');
            const errors = SmartPayRegistration.getErrorMessages();

            if (!$password.length || !$password.val()) return;

            const passwordVal = $password.val();
            const confirmVal = $confirm.val();

            if (confirmVal && passwordVal !== confirmVal) {
                SmartPayRegistration.showFieldError($confirm, errors.password_match);
            } else if (confirmVal) {
                $confirm.removeClass('error');
                $confirm.siblings('.error-message').text('').hide();
            }
        },

        validateCheckbox: function(e) {
            const $checkbox = $(e.target);
            const $group = $checkbox.closest('.checkbox-group');

            if ($checkbox.is(':checked')) {
                $group.find('.error-message').text('').hide();
            }
        },

        togglePassword: function(e) {
            e.preventDefault();
            const $button = $(this);
            const targetName = $button.data('target');
            const $input = $(`[name="${targetName}"]`);

            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $button.attr('aria-label', 'Hide password');

                $button.find('.eye-icon').attr('viewBox', '0 0 24 24').html(`
                    <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="m2 2 20 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                `);
            } else {
                $input.attr('type', 'password');
                $button.attr('aria-label', 'Show password');

                $button.find('.eye-icon').attr('viewBox', '0 0 20 20').html(`
                    <path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
                `);
            }
        }
    };

    $(document).ready(function() {
        SmartPayRegistration.init();
    });

})(jQuery);
