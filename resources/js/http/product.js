import apiFetch from '@wordpress/api-fetch'

export const ProductList = () => {}

export const GetProduct = () => {}

export const SaveProduct = (body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/products`,
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const UpdateProduct = (id, body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/products/${id}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const DeleteProduct = () => {}
