jQuery(document).ready(($) => {
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
		}
	)

	/** ============= Form ============= **/

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
			var selectedAmount = $(e.currentTarget)
				.find('input[name="_form_amount"]')
				.val()
			$(e.currentTarget)
				.parents('.form-amounts')
				.find('#smartpay_custom_amount')
				.val(selectedAmount)
		}
	)
	/** Select form custom amount **/
	$(document.body).on(
		'focus',
		'.smartpay-form-shortcode .form-amounts #smartpay_custom_amount',
		(e) => {
			$(e.currentTarget)
				.parents('.form-amounts')
				.find('.amount')
				.removeClass('selected')
			$(e.currentTarget).addClass('selected')
		}
	)

	/** ============= Payment Modal ============= **/

	/** Open payment form **/
	$(document.body).on(
		'click',
		'.smartpay-payment button.open-payment-form',
		(e) => {
			e.preventDefault()

			let $paymentModal = $(e.currentTarget)
				.parents('.smartpay-payment')
				.find('.payment-modal')

			// Reset payment modal
			resetPaymentModal($paymentModal)

			let buttonText = $(e.currentTarget).text()
			$(e.currentTarget)
				.text('Processing...')
				.attr('disabled', 'disabled')

			setTimeout(() => {
				// Show payment modal
				$paymentModal.modal('show')

				// Appending modal background inside the .smartpay div
				$('.modal-backdrop').appendTo('.smartpay')

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

				jQuery.post(smartpay.ajax_url, data, (response) => {
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

							console.error('Something wrong!')
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
	function getPaymentFormData($wrapper) {
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
			// Product purchase
			smartpay_product_id:
				$wrapper.find('input[name="smartpay_product_id"]').val() ||
				null,
			smartpay_product_variation_id:
				$wrapper
					.find("input[name='smartpay_product_variation_id']:checked")
					.val() || null,

			// Form payment
			smartpay_form_id:
				$wrapper.find('input[name="smartpay_form_id"]').val() || null,
			smartpay_form_amount:
				$wrapper.find('input[name="smartpay_form_amount"]').val() ||
				null,
		}

		// console.log(data)

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
			smartpay_process_payment: { required: true },
			smartpay_gateway: { required: true },
			smartpay_first_name: { required: true, length: [1, 8] },
			smartpay_email: { required: true, email: true },
			smartpay_payment_type: { required: true },
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
                <div class="alert alert-danger py-3">
                    <p class="m-0 form-error-text">${fieldName} ${messages[0]}</p>
                </div>`)
			// })
		})

		if (!errorHTML.length) return

		$wrapper.show()

		$wrapper.html(errorHTML)
	}
})
