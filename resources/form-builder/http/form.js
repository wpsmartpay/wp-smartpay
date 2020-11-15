import apiFetch from '@wordpress/api-fetch'

export const Save = (body) => {
    return apiFetch({
        path: `smartpay/v1/forms/`,
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}
