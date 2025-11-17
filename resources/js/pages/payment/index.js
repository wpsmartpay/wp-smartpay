import { DeletePayment } from '@/http/payment'
import apiFetch from '@wordpress/api-fetch'
import { __ } from '@wordpress/i18n'
import { Container } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DataTable } from '../../components/data-table'
import { createColumns } from './columns'

const { useEffect, useState, useCallback } = wp.element

export const PaymentList = () => {
	const [data, setData] = useState([])
	const [pagination, setPagination] = useState({
		current_page: 1,
		per_page: 10,
		last_page: 1,
		total: 0,
		from: 0,
		to: 0
	})
	const [isLoading, setIsLoading] = useState(false)
	const [searchQuery, setSearchQuery] = useState('')

	// Fetch payments from API
	const fetchPayments = useCallback(async (page = 1, perPage = 10, search = '') => {
		setIsLoading(true)
		try {
			const queryParams = new URLSearchParams({
				page: page,
				per_page: perPage,
				...(search && { search: search })
			})

			const response = await apiFetch({
				path: `${smartpay.restUrl}/v1/payments?${queryParams.toString()}`,
				headers: {
					'X-WP-Nonce': smartpay.apiNonce,
				},
			})

			if (response.payments) {
				// Extract data and pagination info
				const { data: paymentData, ...paginationData } = response.payments

				setData(paymentData || [])
				setPagination({
					current_page: paginationData.current_page,
					per_page: paginationData.per_page,
					last_page: paginationData.last_page,
					total: paginationData.total,
					from: paginationData.from,
					to: paginationData.to
				})
			}
		} catch (error) {
			console.error('Error fetching payments:', error)
			Swal.fire({
				icon: 'error',
				title: __('Error', 'smartpay'),
				text: __('Failed to load payments', 'smartpay'),
			})
		} finally {
			setIsLoading(false)
		}
	}, [])

	// Initial load
	useEffect(() => {
		fetchPayments(1, 10, '')
	}, [fetchPayments])

	// Handle pagination change
	const handlePaginationChange = useCallback(({ page, per_page }) => {
		fetchPayments(page, per_page, searchQuery)
	}, [fetchPayments, searchQuery])

	// Handle search change
	const handleSearchChange = useCallback((search) => {
		setSearchQuery(search)
		// Reset to page 1 when searching
		fetchPayments(1, pagination.per_page, search)
	}, [fetchPayments, pagination.per_page])

	const deletePayment = async (paymentId) => {
		const result = await Swal.fire({
			title: __('Are you sure?', 'smartpay'),
			text: __("You won't be able to revert this!", 'smartpay'),
			icon: 'warning',
			confirmButtonText: __('Yes', 'smartpay'),
			showCancelButton: true,
		});

		if (!result.isConfirmed) return;

		await DeletePayment(paymentId);

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

		fetchPayments(pagination.current_page, pagination.per_page, searchQuery)
	}

	// Create columns with deletePayment function
	const columns = createColumns(deletePayment)

	return (
		<>
			<div className="text-black bg-white border-bottom">
				<Container>
					<div className="d-flex align-items-center justify-content-between py-4">
						<h2 className="text-black m-0">
							{__('Payments', 'smartpay')}
						</h2>
					</div>
				</Container>
			</div>

			<Container className="mt-4">
				<div className="bg-white p-4 rounded-lg shadow-sm">
					<DataTable
						columns={columns}
						data={data}
						pagination={pagination}
						onPaginationChange={handlePaginationChange}
						onSearchChange={handleSearchChange}
						isLoading={isLoading}
						searchPlaceholder='Search by Email or Transaction ID'
					/>
				</div>
			</Container>
		</>
	)
}
