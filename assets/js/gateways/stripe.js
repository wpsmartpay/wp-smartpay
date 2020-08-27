jQuery(document).ready(($) => {
	// Create a Stripe client.
	var stripe = Stripe(smartpay_stripe.publishable_key)

	// Create an instance of Elements.
	var elements = stripe.elements()

	// Custom styling can be passed to options when creating an Element.
	// (Note that this demo uses a wider set of styles than the guide below.)
	var style = {
		base: {
			color: '#32325d',
			fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
			fontSmoothing: 'antialiased',
			fontSize: '16px',
			'::placeholder': {
				color: '#aab7c4',
			},
		},
		invalid: {
			color: '#fa755a',
			iconColor: '#fa755a',
		},
	}

	// Create an instance of the card Element.
	var card = elements.create('card', {
		hidePostalCode: true,
		name: true,
		style: style,
	})

	// Add an instance of the card Element into the `card-element` <div>.
	card.mount('#card-element')

	// Handle real-time validation errors from the card Element.
	card.on('change', function (event) {
		var displayError = document.getElementById('card-errors')
		if (event.error) {
			displayError.textContent = event.error.message
		} else {
			displayError.textContent = ''
		}
	})

	// TODO: Transfer to css class name
	// Handle form submission.
	var form = document.getElementById('stripe-payment-form')
	form.addEventListener('submit', function (event) {
		event.preventDefault()

		stripe.createToken(card).then(function (result) {
			if (result.error) {
				// Inform the user if there was an error.
				var errorElement = document.getElementById('card-errors')
				errorElement.textContent = result.error.message
			} else {
				// Send the token to your server.
				stripeTokenHandler(result.token)
			}
		})
	})

	// Submit the form with the token ID.
	function stripeTokenHandler(token) {
		// Insert the token ID into the form so it gets submitted to the server
		var form = document.getElementById('stripe-payment-form')
		var hiddenInput = document.createElement('input')
		hiddenInput.setAttribute('type', 'hidden')
		hiddenInput.setAttribute('name', 'stripeToken')
		hiddenInput.setAttribute('value', token.id)
		form.appendChild(hiddenInput)

		process_payment()
	}

	function process_payment() {
		const data = {
			action: 'smartpay_stripe_make_payment',
			data: $('#stripe-payment-form').serializeObject(),
		}

		jQuery.post(smartpay.ajax_url, data, (res) => {
			response = JSON.parse(res)

			if (response.status) {
                window.location.replace(response.redirect_to);
			} else {
				console.log('Error: ' + response)
			}
		})
	}

	$.fn.serializeObject = function () {
		var o = {}
		var a = this.serializeArray()
		$.each(a, function () {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]]
				}
				o[this.name].push(this.value || '')
			} else {
				o[this.name] = this.value || ''
			}
		})
		return o
	}
})
