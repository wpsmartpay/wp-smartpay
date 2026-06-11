import apiFetch from '@wordpress/api-fetch'

export const MonthlyReport = () => {
    return apiFetch({
        url: `${smartpay.restUrl}/v1/reports`,
        method: 'GET',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    })
}
