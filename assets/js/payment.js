jQuery(document).ready(($) => {
    /** ============= Product ============= **/

    /** Select product variation **/
    $(document.body).on(
        'click',
        '.smartpay-product-shortcode .product-variations .variation',
        (e) => {
            // e.preventDefault()

            $(e.currentTarget)
                .parent()
                .find('.variation')
                .removeClass('selected')

            $(e.currentTarget).addClass('selected')
        }
    )

    /** ============= Form ============= **/


    /** ============= Payment Modal ============= **/

    /** Open payment form **/
    $(document.body).on(
        'click',
        '.smartpay-payment button.open-payment-form',
        (e) => {
            e.preventDefault()

            let $paymentModal = $(e.currentTarget).parents('.smartpay-payment').find('.payment-modal')

            // Reset payment modal
            resetPaymentModal($paymentModal)

            let buttonText = $(e.currentTarget).text()
            $(e.currentTarget).text('Processing...').attr('disabled', 'disabled')

            setTimeout(() => {
                // Show payment modal
                $paymentModal.modal('show')

                // Appending modal background inside the .smartpay div
                $('.modal-backdrop').appendTo('.smartpay');

                document.body.style.overflow = 'hidden';

                // Reset button
                $(e.currentTarget).text(buttonText).removeAttr('disabled')
            }, 500)

        }
    )

    /** Back to payment modal first step **/
    $(document.body).on('click', '.smartpay-payment button.back-to-first-step', async (e) => {
        e.preventDefault()

        let $paymentModal = $(e.currentTarget).parents('.smartpay-payment').find('.payment-modal')

        // Reset payment modal
        resetPaymentModal($paymentModal)
    })


    /** Send ajax request to process payment **/
    $(document.body).on('click', '.smartpay-payment button.smartpay-pay-now', async (e) => {
        e.preventDefault()

        let buttonText = $(e.currentTarget).text()
        let $paymentFirstStep = $(e.currentTarget).parents('.step-1')
        let $paymentSecondStep = $(e.currentTarget).parents('.modal-content').children('.step-2')

        $(e.currentTarget).text('Processing...').attr('disabled', 'disabled')

        setTimeout(() => {
            // Set loading content
            $paymentSecondStep.find('.dynamic-content').html(`
            <div class="text-center">
                    <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>`)

            $('.back-to-first-step').show()

            // Show second step
            $paymentSecondStep.css('display', 'flex');

            // Hide first step
            $paymentFirstStep.hide();
        }, 500)

        $parentWrapper = $(e.currentTarget).parents('.smartpay-payment');

        let data = {
            action: 'smartpay_process_payment',
            data: getPaymentData($parentWrapper),
        }

        jQuery.post(smartpay.ajax_url, data, (response) => {
            if (response) {
                setTimeout(() => {
                    $paymentSecondStep.find('.dynamic-content').html(
                        response
                    )
                }, 500)
            } else {
                $paymentSecondStep.find('.dynamic-content').html(
                    `<p class="text-danger">Something wrong!</p>`
                )

                console.error('Something wrong!')
            }

            $(e.currentTarget).text(buttonText).removeAttr('disabled')
        })
    })

    function resetPaymentModal($modal) {
        $modal.find('.step-1').show();
        $modal.find('.step-2').hide();
        $('.back-to-first-step').hide()
    }

    function getPaymentData($wrapper) {

        let data = {
            smartpay_action: 'smartpay_process_payment',
            smartpay_process_payment: $wrapper.find('input[name="smartpay_process_payment"]').val() || '',
            smartpay_payment_type: $wrapper.find('input[name="smartpay_payment_type"]').val() || '',
            smartpay_product_id: $wrapper.find('input[name="smartpay_product_id"]').val() || 0,
            smartpay_product_variation_id: $wrapper.find("input[name='smartpay_product_variation_id']:checked").val() || 0,
            smartpay_form_id: $wrapper.find('input[name="smartpay_form_id"]').val() || 0,
            smartpay_gateway: $wrapper.find('input[name="smartpay_gateway"]:checked').val() || '',
            smartpay_first_name: $wrapper.find('input[name="smartpay_first_name"]').val() || '',
            smartpay_last_name: $wrapper.find('input[name="smartpay_last_name"]').val() || '',
            smartpay_email: $wrapper.find('input[name="smartpay_email"]').val() || '',
        }

        // console.log('Getting data')
        console.log(data)

        return data;
    }

    $(document.body).on('click', '.smartpay-payment button.modal-close', async (e) => {
        let $paymentModal = $(e.currentTarget).parents('.smartpay-payment').find('.payment-modal')
        // Show payment modal
        $paymentModal.modal('hide')
    })

    $('.payment-modal').on('hidden.bs.modal', function (e) {
        document.body.style.overflow = 'auto';
    })
})
