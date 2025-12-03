import apiFetch from '@wordpress/api-fetch'
import { __ } from '@wordpress/i18n'
import Swal from 'sweetalert2/dist/sweetalert2'

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
			path: `${smartpay.restUrl}/v1/coupons/${couponId}`,
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

	const response = await apiFetch({
		path: `${smartpay.restUrl}/v1/coupons?${queryParams.toString()}`,
		headers: {
			'X-WP-Nonce': smartpay.apiNonce,
		},
	})

	return response?.coupons || {};
}
