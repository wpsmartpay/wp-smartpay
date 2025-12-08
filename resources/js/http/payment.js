import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import Swal from 'sweetalert2/dist/sweetalert2.js';

export const DeletePayment = async (paymentId) => {
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
			path: `${smartpay.restUrl}/v1/payments/${paymentId}`,
			method: 'DELETE',
			headers: {
				'X-WP-Nonce': smartpay.apiNonce,
			},
		})

		wp.data.dispatch('smartpay/payments').deletePayment(paymentId);

		Swal.fire({
			toast: true,
			icon: 'success',
			title: __('Payment deleted successfully', 'smartpay'),
			position: 'top-end',
			showConfirmButton: false,
			timer: 2000,
			showClass: {
				popup: 'swal2-noanimation',
			},
			hideClass: {
				popup: '',
			},
		})
		return true;
	} catch (error) {
		Swal.fire({
			icon: 'error',
			title: __('Error', 'smartpay'),
			text: error.message || __('Failed to delete payment', 'smartpay'),
		})
	}
}

export const Update = (paymentId, body) => {
    return apiFetch({
        path: `${smartpay.restUrl}/v1/payments/${paymentId}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const GetPayments = async ({ page = 1, perPage = 10, search = '', status = '', type = '', customerId = '', sortBy = 'id:desc' }) => {
	const queryParams = new URLSearchParams({
        page,
        per_page: perPage,
        status,
        type,
        sort_by: sortBy,
        ...(search && { search }),
        ...(customerId && { customer_id: customerId })
	})

	const response = await apiFetch({
		path: `${smartpay.restUrl}/v1/payments?${queryParams.toString()}`,
		headers: {
			'X-WP-Nonce': smartpay.apiNonce,
		},
	})

	// Return payments data along with payment_stats if available
	const paymentsData = response?.payments || {};
	if (response?.payment_stats) {
		return {
			...paymentsData,
			payment_stats: response.payment_stats
		};
	}

	return paymentsData;
}
