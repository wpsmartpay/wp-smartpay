import apiFetch from '@wordpress/api-fetch'

export const SaveCoupon = (body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/coupons`,
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const UpdateCoupon = (id, body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/coupons/${id}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}
