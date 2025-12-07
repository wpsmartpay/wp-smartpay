import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import Swal from 'sweetalert2/dist/sweetalert2';

export const DeleteCustomer = async (customerId) => {
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
			path: `${smartpay.restUrl}/v1/customers/${customerId}`,
			method: 'DELETE',
			headers: {
				'X-WP-Nonce': smartpay.apiNonce,
			},
		})

		wp.data.dispatch('smartpay/customers').deleteCustomer(customerId);

		Swal.fire({
			toast: true,
			icon: 'success',
			title: __('Customer deleted successfully', 'smartpay'),
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
			text: error.message || __('Failed to delete customer', 'smartpay'),
		})
	}
}

export const GetCustomers = async ({ page = 1, perPage = 10, search = '' }) => {
	const queryParams = new URLSearchParams({
		page,
		per_page: perPage,
		...(search && { search })
	})

	const response = await apiFetch({
		path: `${smartpay.restUrl}/v1/customers?${queryParams.toString()}`,
		method: 'GET',
		headers: {
			'X-WP-Nonce': smartpay.apiNonce,
		},
	})
	return response?.customers || {};
}
