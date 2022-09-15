const { SUBSCRIPTION } = require('../../utils/constant')

jQuery(($) => {
    /** Select form fixed amount **/
    $(document.body).on(
        'click',
        '.smartpay-form-shortcode .form-amounts .form-plan-card',
        (e) => {
            // e.preventDefault()
            $(e.currentTarget)
                .parents('.form-amounts')
                .find('.plan-amount')
                .removeClass('selected')

            $(e.currentTarget).addClass('selected')

            // Change the custom amount value on selecting form amount
            var selectedAmount = $(e.currentTarget).find(
                'input[name="_form_amount"]'
            )

            var selectedPriceType = $(e.currentTarget).find(
                'input[name="_form_billing_type"]'
            )

            if (SUBSCRIPTION === selectedPriceType.val()) {
                var selectedBillingPeriod = $(e.currentTarget).find(
                    'input[name="_form_billing_period"]'
                )

                var selectedAmountKey = $(e.currentTarget).find(
                    'input[name="_form_amount_key"]'
                )
            }

            $(e.currentTarget)
                .parents('.form-amounts')
                .find('.form--custom-amount')
                .val(selectedAmount.val())

            $(e.currentTarget)
                .parents('.form-amounts')
                .find('input[name="smartpay_form_billing_type"]')
                .val(selectedPriceType.val())

            if (SUBSCRIPTION === selectedPriceType.val()) {
                $(e.currentTarget)
                    .parents('.form-amounts')
                    .find('input[name="smartpay_form_billing_period"]')
                    .val(selectedBillingPeriod.val())

                $('#smartpay-payment-form')
                    .find('input[name="smartpay_selected_amount_key"]')
                    .val(selectedAmountKey.val())
            }
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
        '.smartpay-form-shortcode button.smartpay-form-pay-now',
        (e) => {
            e.preventDefault()

            $parentWrapper = $(e.currentTarget).parents('.smartpay-payment')

            let buttonText = $(e.currentTarget).text()

            $(e.currentTarget).text('Processing...').attr('disabled', true)

            let formData = getPaymentFormData($parentWrapper)
            let validation = checkPaymentFormValidation(formData)

            // Hide all errors
            $parentWrapper.find('input').removeClass('is-invalid')
            $parentWrapper.find('#form-response').hide()

            if (!validation.valid) {
                showErrors(
                    $parentWrapper.find('.smartpay-message-info'),
                    validation
                )
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
                $(e.currentTarget).text(buttonText).attr('disabled', false)
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

    /** Coupon Form */
    $('.smartpay-coupon-form-toggle .smartpayshowcoupon').on('click', function (
        event
    ) {
        // $('.smartpay-coupon-form').toggleClass('d-none')
        $(this).parents('.smartpay-coupon-form-toggle').addClass('d-none')
        $('.smartpay-coupon-form').removeClass('d-none')
        event.preventDefault()
    })

    let $couponData
    let $currency
    $('.smartpay-coupon-form').on('submit', function (e) {
        let $couponCode = $(this).find('input[name=coupon_code]').val()
        let $formID = $(this)
            .parents('.smartpay_form_builder_wrapper')
            .find('#smartpay-payment-form input[name=smartpay_form_id]')
            .val()

        $.ajax({
            method: 'POST',
            url: smartpay.ajaxUrl,
            data: {
                action: 'smartpay_coupon',
                couponCode: $couponCode,
                formId: $formID,
            },
        }).done(function (response) {
            if (response.success) {
                $('.smartpay-message-info').append(
                    `<div class="alert alert-success">${response.data.message}</div>`
                )

                $couponData = response.data.couponData
                $currency = response.data.currency

                $('#smartpay-payment-form').addClass('coupon-applied')

                $('#smartpay-payment-form')
                    .find('.form--fixed-amount')
                    .each(function () {
                        let $inputId = $(this)
                            .find('input[name=_form_amount]')
                            .attr('id')
                        $(this)
                            .find('input[name=_form_amount]')
                            .val($couponData[$inputId].discountAmount)
                    })

                let $selectedAmountInputId = $(
                    '#smartpay-payment-form .form-amounts'
                )
                    .find(
                        '.form--fixed-amount.selected input[name=_form_amount]'
                    )
                    .attr('id')

                $(
                    '#smartpay-payment-form input[name=smartpay_form_amount]'
                ).val($couponData[$selectedAmountInputId].discountAmount)

                $('.discount-amounts-container').removeClass('d-none')

                $('.discount-amounts-container')
                    .find('.subtotal-amount-value')
                    .html(
                        `${$currency}${$couponData[$selectedAmountInputId].mainAmount}`
                    )

                $('.discount-amounts-container')
                    .find('.coupon-amount-name')
                    .html(response.data.couponCode)

                $('.discount-amounts-container')
                    .find('.coupon-amount-value')
                    .html(
                        `-${$currency}${$couponData[$selectedAmountInputId].couponAmount}`
                    )

                $('.discount-amounts-container')
                    .find('.total-amount-value')
                    .html(
                        `${$currency}${$couponData[$selectedAmountInputId].discountAmount}`
                    )
            }

            if (!response.success) {
                $('.smartpay-message-info').append(
                    `<div class="alert alert-danger">${response.data.message}</div>`
                )
            }
        })
        e.preventDefault()
    })

    $('.smartpay-form-shortcode .form-amounts .form--fixed-amount').on(
        'click',
        function () {
            if ($('#smartpay-payment-form').hasClass('coupon-applied')) {
                let $selectAmountInputId = $(this)
                    .find('input[name=_form_amount]')
                    .attr('id')

                $('.discount-amounts-container')
                    .find('.subtotal-amount-value')
                    .html(
                        `${$currency}${$couponData[$selectAmountInputId].mainAmount}`
                    )

                $('.discount-amounts-container')
                    .find('.coupon-amount-value')
                    .html(
                        `-${$currency}${$couponData[$selectAmountInputId].couponAmount}`
                    )

                $('.discount-amounts-container')
                    .find('.total-amount-value')
                    .html(
                        `${$currency}${$couponData[$selectAmountInputId].discountAmount}`
                    )
            }
        }
    )

    $('.smartpay-coupon-form-close').on('click', function (event) {
        $('.smartpay-coupon-form').addClass('d-none')
        $('.smartpay-coupon-form-toggle').removeClass('d-none')
        event.preventDefault()
    })

    /** Prepare payment data **/
    function getPaymentFormData($wrapper, index = '') {
        const data = $wrapper.find('#smartpay-payment-form').serializeJSON()

        return {
            smartpay_action: 'smartpay_process_payment',
            smartpay_payment_type: 'form_payment',
            smartpay_process_payment: data.smartpay_process_payment,
            smartpay_gateway: data.smartpay_gateway,
            smartpay_first_name: data.smartpay_form.name.first_name,
            smartpay_last_name: data.smartpay_form.name.last_name,
            smartpay_email: data.smartpay_form.email,
            smartpay_payment_mobile: data.smartpay_payment_mobile,
            smartpay_form_id: data.smartpay_form_id,
            smartpay_amount: data.smartpay_form_amount,
            smartpay_amount_key: data.smartpay_selected_amount_key,
            smartpay_form_data: data.smartpay_form,
            smartpay_form_billing_type: data.smartpay_form_billing_type,
            ...(SUBSCRIPTION === data.smartpay_form_billing_type && {
                smartpay_form_billing_period: data.smartpay_form_billing_period,
            }),
        }
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
            smartpay_payment_mobile: {
                requiredWhen: ['smartpay_gateway', 'toyyibpay'],
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
