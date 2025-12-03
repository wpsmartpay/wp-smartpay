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

export const DeleteCoupon = (couponId) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/coupons/${couponId}`,
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    })
}


export const GetCoupons = async ({ page = 1, perPage = 10, search = '', type = '' }) => {
	const queryParams = new URLSearchParams({
        page,
        per_page: perPage,
        type,
        ...(search && { search })
	})

	const response = await apiFetch({
		path: `${smartpay.restUrl}/v1/coupons?${queryParams.toString()}`,
		headers: {
			'X-WP-Nonce': smartpay.apiNonce,
		},
	})

	return response?.coupons || {};
}
