import apiFetch from '@wordpress/api-fetch';

const buildUrl = (endpoint, params = {}) => {
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    const url = new URL(`${baseUrl}/${endpoint}`);
    Object.entries(params).forEach(([k, v]) => {
        if (v !== '' && v !== null && v !== undefined) {
            url.searchParams.set(k, v);
        }
    });
    return url.toString();
};

export const GetFormSubmissions = async ({ page = 1, perPage = 10, search = '', sortBy = 'id:desc' } = {}) => {
    const response = await apiFetch({
        url: buildUrl('v1/payments', {
            page,
            per_page: perPage,
            type: 'form_payment',
            sort_by: sortBy,
            search,
        }),
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    });
    return response?.payments || {};
};

export const GetForms = async ({ page = 1, perPage = 100 } = {}) => {
    const response = await apiFetch({
        url: buildUrl('v1/forms', {
            page,
            per_page: perPage,
        }),
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
    });
    return response?.forms || {};
};
