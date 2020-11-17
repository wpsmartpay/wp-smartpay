import apiFetch from '@wordpress/api-fetch'

export const Save = (body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/coupons`,
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const Update = (id, body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/coupons/${id}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const Delete = (couponId) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/coupons/${couponId}`,
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    })
}
