import apiFetch from '@wordpress/api-fetch'
import { __ } from '@wordpress/i18n'
import Swal from 'sweetalert2/dist/sweetalert2'

// NOTE: use apiFetch `url` (full URL), not `path`. On admin pages wp.apiFetch
// has the rootURL middleware active, which prepends the REST root to `path` —
// a full-URL `path` would double to .../wp-json/http://.../wp-json/... (404,
// "No route was found"). `url` bypasses that middleware and works whether or
// not it is registered. `data` (object) lets apiFetch JSON-encode + set the
// Content-Type header so WP REST parses the body into params.
export const Save = (data) => {
    return apiFetch({
        url: `${smartpay.restUrl}/v1/coupons`,
        method: 'POST',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        data,
    })
}

export const Update = (id, data) => {
    return apiFetch({
        url: `${smartpay.restUrl}/v1/coupons/${id}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        data,
    })
}

export const DeleteCoupon = async (couponId) => {
	const popup = await Swal.fire({
		title: __('Are you sure?', 'smartpay'),
		text: __("You won't be able to revert this!", 'smartpay'),
		icon: 'warning',
		confirmButtonText: __('Yes', 'smartpay'),
		showCancelButton: true,
	});

	if (!popup.isConfirmed) return;

	try {
		await apiFetch({
			url: `${smartpay.restUrl}/v1/coupons/${couponId}`,
			method: 'DELETE',
			headers: {
				'X-WP-Nonce': smartpay.apiNonce,
			},
		})

		wp.data.dispatch('smartpay/coupons').deleteCoupon(couponId);

		Swal.fire({
			toast: true,
			icon: 'success',
			title: __('Coupon deleted successfully', 'smartpay'),
			position: 'top-end',
			showConfirmButton: false,
			timer: 2000,
			showClass: {
				popup: 'swal2-noanimation',
			},
			hideClass: {
				popup: '',
			},
		});
		return true;
	} catch (error) {
		Swal.fire({
			icon: 'error',
			title: __('Error', 'smartpay'),
			text: error.message || __('Failed to delete coupon', 'smartpay'),
		})
	}
}

export const GetCoupons = async ({ page = 1, perPage = 10, search = '', type = '' }) => {
	const queryParams = new URLSearchParams({
        page,
        per_page: perPage,
        type,
        ...(search && { search })
	})

	const url = `${smartpay.restUrl}/v1/coupons`;
    const separator = url.includes('?') ? '&' : '?';

	const response = await apiFetch({
		url: `${url}${separator}${queryParams.toString()}`,
		headers: {
			'X-WP-Nonce': smartpay.apiNonce,
		},
	})

	return response?.coupons || {};
}
