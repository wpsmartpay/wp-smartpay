import apiFetch from '@wordpress/api-fetch'

import { serialize, parse } from '@wordpress/blocks'

export const SaveForm = (body) => {
    return apiFetch({
        path: '/smartpay/v1/forms/',
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: serialize(body),
    })
}
