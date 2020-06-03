jQuery(document).ready(($) => {
	const $paymentModal = $('.smartpay-payment-modal')

	/** Send ajax request to process payment **/
	$(document.body).on('click', '.smartpay button#pay_now', (e) => {
		e.preventDefault()

		// TODO: Change to class name
		let buttonText = $('button#pay_now').text()

		$('#pay_now').text('Processing...').attr('disabled', 'disabled')

		$('#smartpay_payment_checkout_modal .overlay').css('display', 'block')

		$('#smartpay_payment_gateway_modal .modal-body').html(`
            <div class="text-center">
                    <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>`)

		$('#smartpay_payment_gateway_modal').modal('show')

		let data = {
			action: 'smartpay_process_payment',
			data: getFormJSONData($('.smartpay #payment_form')),
		}

		jQuery.post(smartpay.ajax_url, data, (response) => {
			if (response) {
				$paymentModal.modal('hide')

				$('#smartpay_payment_checkout_modal .overlay').css(
					'display',
					'none'
				)

				setTimeout(() => {
					$('#smartpay_payment_gateway_modal .modal-body').html(
						response
					)
				}, 500)
			} else {
				$('#smartpay_payment_gateway_modal .modal-body').html(
					'Something wrong!'
				)

				console.log('Something wrong!')
			}

			$('button#pay_now').text(buttonText).removeAttr('disabled')
		})
	})

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

	/** Open payment form **/
	$(document.body).on(
		'click',
		'.smartpay-product-shortcode button.open-payment-form',
		(e) => {
			e.preventDefault()

			// FIXME: Remove smartpay class
			if (!$('body').hasClass('smartpay')) {
				$('body').addClass('smartpay')
			}

			// TODO: Find dynamically
			$('.smartpay-payment-modal').modal('show')
		}
	)
})
