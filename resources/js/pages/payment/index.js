import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
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
	const [paymentStatus, setPaymentStatus] = useState('')
	const [paymentType, setPaymentType] = useState('')
	const [sortBy, setSortBy] = useState('id:desc')

	// Fetch payments from API
	const fetchPayments = useCallback(async (page = 1, perPage = 10, search = '', status = '', type = '', sortBy = 'id:desc') => {
		setIsLoading(true)
		try {
			const queryParams = new URLSearchParams({
				page: page,
				per_page: perPage,
				status: status,
				type: type,
				sort_by: sortBy,
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

	useEffect(() => {
		fetchPayments(1, pagination.per_page, searchQuery, paymentStatus, paymentType, sortBy)
	}, [fetchPayments, searchQuery, paymentStatus, paymentType, pagination.per_page, sortBy])

	const handlePaginationChange = useCallback(({ page, per_page }) => {
		fetchPayments(page, per_page, searchQuery, paymentStatus, paymentType, sortBy)
	}, [fetchPayments, searchQuery, paymentStatus, paymentType, sortBy])

	const handleSearchChange = (search) => {
		setSearchQuery(search)
	}

	const handleSort = useCallback((sortDetails) => {
		const sortBy = sortDetails.map((detail) => `${detail.id}:${detail.desc ? 'desc' : 'asc'}`).join(',');
		setSortBy(sortBy);
	}, [sortBy]);

	const handleStatusFilter = (status) => {
		if (status === 'all') {
			status = '';
		}
		setPaymentStatus(status);
	}

	const handleTypeFilter = (type) => {
		if (type === 'all') {
			type = '';
		}
		setPaymentType(type);
	}

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

		fetchPayments(pagination.current_page, pagination.per_page, searchQuery, paymentStatus, paymentType, sortBy);
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
						enableSorting={true}
						onSortChange={handleSort}
						isLoading={isLoading}
						searchPlaceholder='Search by Email or Transaction ID'
						enableFilters={true}
						filters={[
							<Select onValueChange={handleStatusFilter}>
								<SelectTrigger className="w-[180px]">
									<SelectValue placeholder="Filter by status" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="all">All</SelectItem>
									<SelectItem value="refunded">Refunded</SelectItem>
									<SelectItem value="completed">Completed</SelectItem>
									<SelectItem value="pending">Pending</SelectItem>
									<SelectItem value="failed">Failed</SelectItem>
									<SelectItem value="processing">Processing</SelectItem>
									<SelectItem value="revoked">Revoked</SelectItem>
									<SelectItem value="abandoned">Abandoned</SelectItem>
								</SelectContent>
							</Select>,
							<Select onValueChange={handleTypeFilter}>
								<SelectTrigger className="w-[180px]">
									<SelectValue placeholder="Filter by Type" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="all">All</SelectItem>
									<SelectItem value="form_payment">Form</SelectItem>
									<SelectItem value="product_purchase">Product</SelectItem>
								</SelectContent>
							</Select>
						]}
					/>
				</div>
			</Container>
		</>
	)
}
