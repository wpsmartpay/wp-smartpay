import apiFetch from '@wordpress/api-fetch'
import { __ } from '@wordpress/i18n'
import Swal from 'sweetalert2/dist/sweetalert2'

export const GetProducts = async ({ page = 1, perPage = 10, search = '', sortBy = 'id:desc', billingType = '' }) => {
	const queryParams = new URLSearchParams({
		page,
		per_page: perPage,
		sort_by: sortBy,
		...(search && { search }),
		...(billingType && { billing_type: billingType }),
	})

    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    const url = `${baseUrl}/v1/products`;
    const separator = url.includes('?') ? '&' : '?';

	const response = await apiFetch({
		url: `${url}${separator}${queryParams.toString()}`,
		headers: {
			'X-WP-Nonce': smartpay.apiNonce,
		},
	})

	return response?.products || {}
}

export const SaveProduct = (body) => {
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    return apiFetch({
        url: `${baseUrl}/v1/products`,
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const UpdateProduct = (id, body) => {
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    return apiFetch({
        url: `${baseUrl}/v1/products/${id}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const DeleteProduct = async (productId) => {
	const popup = await Swal.fire({
		title: __('Are you sure?', 'smartpay'),
		text: __("You won't be able to revert this!", 'smartpay'),
		icon: 'warning',
		confirmButtonText: __('Yes', 'smartpay'),
		showCancelButton: true,
	})

	if (!popup.isConfirmed) return false

	try {
        const baseUrl = smartpay.restUrl.replace(/\/$/, '');
		await apiFetch({
			url: `${baseUrl}/v1/products/${productId}`,
			method: 'DELETE',
			headers: {
				'X-WP-Nonce': smartpay.apiNonce,
			},
		})

		Swal.fire({
			toast: true,
			icon: 'success',
			title: __('Product deleted successfully', 'smartpay'),
			position: 'top-end',
			showConfirmButton: false,
			timer: 2000,
			showClass: { popup: 'swal2-noanimation' },
			hideClass: { popup: '' },
		})
		return true
	} catch (error) {
		Swal.fire({
			icon: 'error',
			title: __('Error', 'smartpay'),
			text: error.message || __('Failed to delete product', 'smartpay'),
		})
		return false
	}
}
