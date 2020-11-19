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
