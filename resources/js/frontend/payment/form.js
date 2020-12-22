jQuery(($) => {
    /** Select form fixed amount **/
    $(document.body).on(
        'click',
        '.smartpay-form-shortcode .form-amounts .form--fixed-amount',
        (e) => {
            // e.preventDefault()
            $(e.currentTarget)
                .parents('.form-amounts')
                .find('.amount')
                .removeClass('selected')

            $(e.currentTarget).addClass('selected')

            // Change the custom amount value on selecting form amount
            var selectedAmount = $(e.currentTarget).find(
                'input[name="_form_amount"]'
            )

            $(e.currentTarget)
                .parents('.form-amounts')
                .find('.form--custom-amount')
                .val(selectedAmount.val())
        }
    )

    /** Select form custom amount **/
    $(document.body).on(
        'focus',
        '.smartpay-form-shortcode .form-amounts .form--custom-amount',
        (e) => {
            $(e.currentTarget)
                .parents('.form-amounts')
                .find('.amount')
                .removeClass('selected')
            $(e.currentTarget).addClass('selected')
        }
    )

    /** Send ajax request to process form payment **/
    $(document.body).on(
        'click',
        '.smartpay-form-shortcode button.smartpay-pay-now',
        (e) => {
            e.preventDefault()

            $parentWrapper = $(e.currentTarget).parents('.smartpay-payment')

            let buttonText = $(e.currentTarget).text()

            $(e.currentTarget).text('Processing...').attr('disabled', true)

            let formData = getPaymentFormData($parentWrapper)
            let validation = checkPaymentFormValidation(formData)
            // console.log(validation)
            // return

            // Hide all errors
            $parentWrapper.find('input').removeClass('is-invalid')
            $parentWrapper.find('#form-response').hide()

            if (!validation.valid) {
                showErrors($parentWrapper.find('#form-response'), validation)
            } else {
                jQuery.post(
                    smartpay.ajaxUrl,
                    {
                        action: 'smartpay_process_payment',
                        data: formData,
                    },
                    (response) => {
                        if (response) {
                            $parentWrapper
                                .find('#payment-response')
                                .html(response)
                                .show()
                        } else {
                            $parentWrapper
                                .find('#payment-response')
                                .html(
                                    `<p class="text-danger">Something wrong! Please try again later.</p>`
                                )
                                .show()

                            console.error('Something wrong!')
                        }
                    }
                )
            }

            setTimeout(() => {
                $(e.currentTarget).text(buttonText).removeAttr('disabled')
            }, 300)
        }
    )

    /** Open form modal */
    $(document.body).on(
        'click',
        '.smartpay-form-shortcode button.open-form-modal',
        (e) => {
            e.preventDefault()

            let $formModal = $(e.currentTarget)
                .parents('.smartpay-form-shortcode')
                .find('.form-modal')

            setTimeout(() => {
                // Show form modal
                $formModal.modal('show')

                // Appending modal background inside the .smartpay div
                $('.modal-backdrop')
                    .last()
                    .appendTo($(e.currentTarget).closest('.smartpay'))
            }, 500)
        }
    )

    /** Prepare payment data **/
    function getPaymentFormData($wrapper, index = '') {
        let data = {
            smartpay_action: 'smartpay_process_payment',
            smartpay_process_payment:
                $wrapper.find('input[name="smartpay_process_payment"]').val() ||
                null,
            smartpay_gateway:
                $wrapper.find('input[name="smartpay_gateway"]:checked').val() ||
                null,
            smartpay_first_name:
                $wrapper
                    .find('input[name="smartpay_form[name][first_name]"]')
                    .val() || null,
            smartpay_last_name:
                $wrapper
                    .find('input[name="smartpay_form[name][last_name]"]')
                    .val() || null,
            smartpay_email:
                $wrapper.find('input[name="smartpay_form[email]"]').val() ||
                null,
            smartpay_payment_type:
                $wrapper.find('input[name="smartpay_payment_type"]').val() ||
                null,
            smartpay_form_id:
                $wrapper.find('input[name="smartpay_form_id"]').val() || null,
            smartpay_amount:
                $wrapper.find('input[name="smartpay_form_amount"]').val() ||
                null,
        }

        let smartpay_form_data = {}

        $wrapper
            .find('form input[name^="smartpay_form["]')
            .each(function (index, item) {
                smartpay_form_data[$(item).attr('name')] = $(item).val()
            })

        data.form_response = smartpay_form_data || {}

        return data
    }

    function checkPaymentFormValidation(data) {
        const rules = {
            smartpay_action: {
                required: true,
                value: 'smartpay_process_payment',
            },
            smartpay_process_payment: {
                required: true,
            },
            smartpay_gateway: {
                required: true,
            },
            smartpay_first_name: {
                required: true,
            },
            smartpay_last_name: {
                required: true,
            },
            smartpay_email: {
                required: true,
                email: true,
            },
            smartpay_payment_type: {
                required: true,
            },
        }

        const validator = new SmartPayFormValidator(data, rules)

        const errors = validator.validate()

        return {
            valid: Object.values(errors).every(
                (messages) => messages.length === 0
            ),
            errors: errors,
        }
    }

    function showErrors($wrapper, validation) {
        const $parentWrapper = $wrapper.parents('.smartpay-payment')
        const errorHTML = []

        Object.entries(validation.errors).forEach(([property, messages]) => {
            $parentWrapper
                .find('input[name="' + property + '"]')
                .addClass('is-invalid')

            let fieldName = JSUcfirst(property.split('_').slice(1).join(' '))

            // messages.forEach((message) => {
            errorHTML.push(`
                <div class="alert alert-danger">
                    <p class="m-0 form-error-text">${fieldName} ${messages[0]}</p>
                </div>`)
            // })
        })

        if (!errorHTML.length) {
            return
        }

        $wrapper.html(errorHTML)

        $wrapper.show()
    }
})
