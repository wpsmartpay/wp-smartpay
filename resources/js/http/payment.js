import apiFetch from '@wordpress/api-fetch'

export const DeletePayment = (paymentId) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/payments/${paymentId}`,
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    })
}

export const Update = (paymentId, body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/payments/${paymentId}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}
