import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import Swal from 'sweetalert2/dist/sweetalert2';

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
        const baseUrl = smartpay.restUrl.replace(/\/$/, '');
		await apiFetch({
			url: `${baseUrl}/v1/payments/${paymentId}`,
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
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    return apiFetch({
        url: `${baseUrl}/v1/payments/${paymentId}`,
        method: 'PUT',
        headers: {
            'X-WP-Nonce': smartpay.apiNonce,
        },
        body: body,
    })
}

export const GetPayment = async (paymentId) => {
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    const response = await apiFetch({
        url: `${baseUrl}/v1/payments/${paymentId}`,
        headers: { 'X-WP-Nonce': smartpay.apiNonce },
    });
    return response?.payment || null;
}

export const GetPaymentLogs = async (paymentId, page = 1, perPage = 20) => {
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    const url = new URL(`${baseUrl}/v1/payments/${paymentId}/logs`);
    url.searchParams.set('page', page);
    url.searchParams.set('per_page', perPage);
    const response = await apiFetch({
        url: url.toString(),
        headers: { 'X-WP-Nonce': smartpay.apiNonce },
    });
    return response;
}

export const AddPaymentLog = async (paymentId, note) => {
    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    return apiFetch({
        url: `${baseUrl}/v1/payments/${paymentId}/logs`,
        method: 'POST',
        headers: { 'X-WP-Nonce': smartpay.apiNonce },
        body: JSON.stringify({ note }),
    });
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

    const baseUrl = smartpay.restUrl.replace(/\/$/, '');
    const url = `${baseUrl}/v1/payments`;
    const separator = url.includes('?') ? '&' : '?';

	const response = await apiFetch({
		url: `${url}${separator}${queryParams.toString()}`,
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
