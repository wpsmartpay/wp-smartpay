import apiFetch from '@wordpress/api-fetch'

export const DeleteCustomer = (customerId) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/customers/${customerId}`,
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    })
}
