jQuery(document).ready(($) => {
    /** ============= Product ============= **/

    /** Select product variation **/
    $(document.body).on(
        'click',
        '.smartpay-product-shortcode .product-variations .variation',
        (e) => {
            e.preventDefault()

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

                // Reset button
                $(e.currentTarget).text(buttonText).removeAttr('disabled')
            }, 500)

        }
    )

    /** Send ajax request to process payment **/
    $(document.body).on('click', '.smartpay-payment button.smartpay-pay-now', async (e) => {
        e.preventDefault()

        let buttonText = $(e.currentTarget).text()
        let $paymentFirstStep = $(e.currentTarget).parents('.step-1')
        let $paymentSecondStep = $(e.currentTarget).parents('.modal-content').children('.step-2')

        $(e.currentTarget).text('Processing...').attr('disabled', 'disabled')

        setTimeout(() => {
            // Set loading content
            $paymentSecondStep.html(`
            <div class="text-center">
                    <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>`)

            // Show second step
            $paymentSecondStep.show();

            // Hide first step
            $paymentFirstStep.hide();
        }, 500)


        let data = {
            action: 'smartpay_process_payment',
            // data: getFormJSONData($('.smartpay #payment_form')),
        }

        jQuery.post(smartpay.ajax_url, data, (response) => {
            if (response) {
                setTimeout(() => {
                    $paymentSecondStep.html(
                        response
                    )
                }, 500)
            } else {
                $paymentSecondStep.html(
                    `<p class="text-danger">Something wrong!</p>`
                )

                console.log('Something wrong!')
            }

            $(e.currentTarget).text(buttonText).removeAttr('disabled')
        })
    })

    function resetPaymentModal($modal) {
        $modal.find('.step-1').show();
        $modal.find('.step-2').hide();
    }

})
