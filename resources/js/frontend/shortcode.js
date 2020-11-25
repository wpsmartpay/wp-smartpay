import apiFetch from '@wordpress/api-fetch'
// user info update
jQuery(document.body).on(
    'click',
    '.customer-dashboard button[type=submit]',
    function (e) {
        e.preventDefault()
        const $parentWrapper = jQuery(this).parents('form')
        let data = {
            first_name:
                $parentWrapper.find('input[name=first_name]').val() || null,
            last_name:
                $parentWrapper.find('input[name=last_name]').val() || null,
            email: $parentWrapper.find('input[name=email]').val() || null,
            password: $parentWrapper.find('input[name=password]').val() || null,
            password_confirm:
                $parentWrapper.find('input[name=password_confirm]').val() ||
                null,
        }

        const customerId =
            $parentWrapper.find('input[name=customer_id]').val() || 0

        apiFetch({
            path: `${smartpay.restUrl}/v1/public/customers/${customerId}`,
            method: 'PUT',
            headers: {
                'X-WP-Nonce': smartpay.apiNonce,
            },
            body: JSON.stringify(data),
        })
            .then((response) => {
                $parentWrapper.append(
                    `<div class="alert alert-success text-center mb-4">${response.message}</div>`
                )
            })
            .catch((error) => {
                $parentWrapper.append(
                    `<div class="alert alert-danger text-center mb-4">${error.message}</div>`
                )
            })
    }
)
