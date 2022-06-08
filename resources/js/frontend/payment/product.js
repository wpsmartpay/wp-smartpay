jQuery(($) => {
    /** ============= Product ============= **/

    /** Select product variation **/
    $(document.body).on(
        'click',
        '.smartpay-product-shortcode .product-variations .variation',
        (e) => {
            $(e.currentTarget)
                .parent()
                .find('.variation')
                .removeClass('selected')

            $(e.currentTarget).addClass('selected')

            let $salePrice = $(e.currentTarget).find('.sale-price').html()
            $salePrice = $salePrice.replace(/\$/, '')
            $(e.currentTarget)
                .parents('.smartpay-product-shortcode')
                .find('input[name="smartpay_product_price"]')
                .val($salePrice)

            let selectedPriceType = $(e.currentTarget).find(
                'input[name="_product_billing_type"]'
            )

            $(e.currentTarget)
                .parents('.smartpay-product-shortcode')
                .find('input[name="smartpay_product_billing_type"]')
                .val(selectedPriceType.val())

            if ('Subscription' === selectedPriceType.val()) {
                const selectedBillingPeriod = $(e.currentTarget).find(
                    'input[name="_product_billing_period"]'
                );

                $(e.currentTarget)
                    .parents('.smartpay-product-shortcode')
                    .find('input[name="smartpay_product_billing_period"]')
                    .val(selectedBillingPeriod.val())
            }

            let selectedProductId = $(e.currentTarget).find(
                'input[name="_smartpay_product_id"]'
            )

            $(e.currentTarget)
                .parents('.smartpay-product-shortcode')
                .find('input[name="smartpay_product_id"]')
                .val(selectedProductId.val())
        }
    )

    $(document).ready(() => {

        const container = document.querySelector('#mobile-field');
        if (!container) return;

        const checkedValue = $("input[name='smartpay_gateway']:checked").val();

        const mobile_field_html = '<div class="form-group">' +
            '<input type="number" ' +
            'placeholder="Mobile No" class="form-control" ' +
            'name="smartpay_payment_mobile"  ' +
            'id="smartpay_payment_mobile" required />' +
            '</div>';

        if (checkedValue === "toyyibpay") {
            container.innerHTML = mobile_field_html
        }
        const input = jQuery('input[name="smartpay_gateway"]').toArray()
        input.forEach((item) => {
            item.addEventListener('change', () => {
                if (item.value === 'toyyibpay') {
                    container.innerHTML = mobile_field_html
                } else {
                    container.innerHTML = ''
                }
            })
        })
    })

    /** Open product modal */
    $(document.body).on(
        'click',
        '.smartpay-product-shortcode button.open-product-modal',
        (e) => {
            e.preventDefault()

            let $productModal = $(e.currentTarget)
                .parents('.smartpay-product-shortcode')
                .find('.product-modal')

            setTimeout(() => {
                $productModal.modal('show')

                // Appending modal background inside the .smartpay div
                $('.modal-backdrop')
                    .last()
                    .appendTo($(e.currentTarget).closest('.smartpay'))
            }, 500)
        }
    )

    /** ============= Payment Modal ============= **/

    /** Open payment form **/
    $(document.body).on(
        'click',
        '.smartpay-payment button.open-payment-form',
        (e) => {
            e.preventDefault()
            $parentWrapper = $(e.currentTarget).parents('.smartpay-payment')

            let $paymentModal = $parentWrapper.find('.payment-modal')
            let formData = getPaymentFormData($parentWrapper)

            // show the billing type on Payment modal
            let billing_period = jQuery('input[name="smartpay_product_billing_period"]').val()
            $paymentModal.find('.product_billing_type').html(billing_period)

            // Set payment amount
            let paymentAmount = 0
            if ('form_payment' === formData.smartpay_payment_type) {
                const currencySymbol = $('#smartpay_currency_symbol').data(
                    'value'
                )
                paymentAmount = currencySymbol + formData.smartpay_amount
            } else {
                paymentAmount = formData.smartpay_product_price
            }
            $paymentModal.find('.amount').html(paymentAmount)

            // Reset payment modal
            resetPaymentModal($paymentModal)

            let buttonText = $(e.currentTarget).text()
            $(e.currentTarget)
                .text('Processing...')
                .attr('disabled', 'disabled')

            setTimeout(() => {
                $paymentModal.modal('show')

                if (formData.smartpay_first_name) {
                    $paymentModal
                        .find('input[name="smartpay_first_name"]')
                        .val(formData.smartpay_first_name)
                }

                if (formData.smartpay_last_name) {
                    $paymentModal
                        .find('input[name="smartpay_last_name"]')
                        .val(formData.smartpay_last_name)
                }

                if (formData.smartpay_email) {
                    $paymentModal
                        .find('input[name="smartpay_email"]')
                        .val(formData.smartpay_email)
                }

                // Appending modal background inside the .smartpay div
                $('.modal-backdrop')
                    .last()
                    .appendTo($(e.currentTarget).closest('.smartpay-payment'))

                // Reset button
                $(e.currentTarget).text(buttonText).removeAttr('disabled')
            }, 500)
        }
    )

    /** Back to payment modal first step **/
    $(document.body).on(
        'click',
        '.smartpay-payment button.back-to-first-step',
        (e) => {
            e.preventDefault()

            let $paymentModal = $(e.currentTarget)
                .parents('.smartpay-payment')
                .find('.payment-modal')

            // Reset payment modal
            resetPaymentModal($paymentModal)
        }
    )

    /** Send ajax request to process payment **/
    $(document.body).on(
        'click',
        '.smartpay-payment button.smartpay-pay-now',
        (e) => {
            e.preventDefault()
            $parentWrapper = $(e.currentTarget).parents('.smartpay-payment')

            let buttonText = $(e.currentTarget).text()
            let $paymentFirstStep = $(e.currentTarget).parents('.step-1')
            let $paymentSecondStep = $(e.currentTarget)
                .parents('.modal-content')
                .children('.step-2')

            $(e.currentTarget)
                .text('Processing...')
                .attr('disabled', 'disabled')

            $parentWrapper.find('.modal-loading').css('display', 'flex')

            let formData = getPaymentFormData($parentWrapper)
            let validation = checkPaymentFormValidation(formData)

            // Hide all errors
            $parentWrapper.find('input').removeClass('is-invalid')
            $paymentFirstStep.find('.payment-modal--errors').hide()

            if (!validation.valid) {
                showErrors(
                    $paymentFirstStep.find('.payment-modal--errors'),
                    validation
                )
                setTimeout(() => {
                    $parentWrapper.find('.modal-loading').css('display', 'none')
                }, 300)
            } else {
                let data = {
                    action: 'smartpay_process_payment',
                    data: formData,
                }
                jQuery.post(smartpay.ajaxUrl, data, (response) => {
                    // Show second step
                    $paymentSecondStep.css('display', 'flex')

                    $('.back-to-first-step').show()

                    // Hide first step
                    $paymentFirstStep.hide()

                    setTimeout(() => {
                        if (response) {
                            $paymentSecondStep
                                .find('.dynamic-content')
                                .html(response)
                        } else {
                            $paymentSecondStep
                                .find('.dynamic-content')
                                .html(
                                    `<p class="text-danger">Something wrong!</p>`
                                )
                        }

                        $parentWrapper
                            .find('.modal-loading')
                            .css('display', 'none')
                    }, 300)
                })
            }

            $(e.currentTarget).text(buttonText).removeAttr('disabled')
        }
    )

    /** Go to first step of payment **/
    function resetPaymentModal($modal) {
        $modal.find('.step-1').show()
        $modal.find('.step-2').hide()
        $('.back-to-first-step').hide()
    }

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
                $wrapper.find('input[name="smartpay_first_name"]').val() ||
                null,
            smartpay_last_name:
                $wrapper.find('input[name="smartpay_last_name"]').val() || null,
            smartpay_email:
                $wrapper.find('input[name="smartpay_email"]').val() || null,

            smartpay_payment_type:
                $wrapper.find('input[name="smartpay_payment_type"]').val() ||
                null,
            smartpay_payment_mobile:
                $wrapper.find('input[name="smartpay_payment_mobile"]').val() || null,
        }

        if ('product_purchase' === data.smartpay_payment_type) {
            data.smartpay_product_id =
                $wrapper.find('input[name="smartpay_product_id"]').val() || null
            data.smartpay_product_price =
                $wrapper.find('input[name="smartpay_product_price"]').val() ||
                null
            data.smartpay_product_billing_type =
                $wrapper
                    .find('input[name="smartpay_product_billing_type"]')
                    .val() || null
            if (data.smartpay_product_billing_type === 'Subscription') {
                data.smartpay_product_billing_period = $wrapper
                    .find('input[name="smartpay_product_billing_period"]')
                    .val()
            }
        } else {
            data.smartpay_form_id =
                $wrapper.find('input[name="smartpay_form_id"]').val() || null
            data.smartpay_amount =
                $wrapper.find('input[name="smartpay_form_amount"]').val() ||
                null

            let smartpay_form_extra_data = {}
            if (
                $wrapper.find(
                    '.smartpay_form_builder_wrapper > form input[name^="smartpay_"]'
                ).length
            ) {
                $wrapper
                    .find(
                        '.smartpay_form_builder_wrapper > form input[name^="smartpay_"]'
                    )
                    .each(function (index, item) {
                        smartpay_form_extra_data[$(item).attr('name')] = $(
                            item
                        ).val()
                    })
            }

            data.smartpay_form_extra_data = smartpay_form_extra_data || {}
        }

        if (index) {
            return data.index || null
        }

        return data
    }

    /** Close payment payment modal **/
    $(document.body).on(
        'click',
        '.smartpay-payment button.modal-close',
        (e) => {
            let $paymentModal = $(e.currentTarget)
                .parents('.smartpay-payment')
                .find('.payment-modal')
            // Show payment modal
            $paymentModal.modal('hide')
        }
    )

    /** Handle payment modal open event **/
    $(document.body).on('show.bs.modal', '.payment-modal', (e) => {
        document.body.style.overflow = 'hidden'
    })

    /** Handle payment modal close event **/
    $(document.body).on('hidden.bs.modal', '.payment-modal', (e) => {
        document.body.style.overflow = 'auto'
    })

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
            smartpay_payment_mobile: {
                requiredWhen: ['smartpay_gateway', 'toyyibpay'],
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

        if (!errorHTML.length) return

        $wrapper.show()

        $wrapper.html(errorHTML)
    }

    /** Coupon Form */
    $('.smartpay-product-coupon-form-toggle .smartpayshowcoupon').on(
        'click',
        function () {
            $('.smartpay-product-coupon-form').toggleClass('d-none')
            return false
        }
    )

    $('.smartpay-product-coupon-form').on('submit', function (e) {
        let $couponCode = $(this).find('input[name=coupon_code]').val()
        let $productID = $(this)
            .parents('.smartpay-product-shortcode')
            .find('input[name=smartpay_product_id]')
            .val()
        let $productPrice = $(this)
            .parents('.smartpay-product-shortcode')
            .find('input[name=smartpay_product_price]')
            .val()
        $.ajax({
            method: 'POST',
            url: smartpay.ajaxUrl,
            data: {
                action: 'smartpay_product_coupon',
                couponCode: $couponCode,
                productID: $productID,
                productPrice: $productPrice,
            },
        }).done(function (response) {
            if (response.success) {
                // hide the previous alert
                $('.payment-modal--errors .alert').hide()
                $('.payment-modal--errors').append(
                    `<div class="alert alert-success">${response.data.message}</div>`
                )
                $('.payment-modal--errors').show()

                $couponData = response.data.couponData
                $currency = response.data.currency

                $('.discount-amounts-container')
                    .find('.subtotal-amount-value')
                    .html(`${$currency}${$couponData.mainAmount}`)

                $('.discount-amounts-container')
                    .find('.coupon-amount-name')
                    .html(response.data.couponCode)

                $('.discount-amounts-container')
                    .find('.coupon-amount-value')
                    .html(`-${$currency}${$couponData.couponAmount}`)

                $('.discount-amounts-container')
                    .find('.total-amount-value')
                    .html(`${$currency}${$couponData.discountAmount}`)

                $('.smartpay-product-shortcode')
                    .find('input[name=smartpay_product_price]')
                    .val(`${$couponData.discountAmount}`)

                $('.smartpay-product-shortcode')
                    .find('.payment-modal--title')
                    .html(`${$couponData.discountAmount}`)

                $('.discount-amounts-container').removeClass('d-none')
            }
            if (!response.success) {
                // hide the previous alert
                $('.payment-modal--errors .alert').hide()
                $('.payment-modal--errors').append(
                    `<div class="alert alert-danger">${response.data.message}</div>`
                )
                $('.payment-modal--errors').show()
            }
        })
        e.preventDefault()
    })
})