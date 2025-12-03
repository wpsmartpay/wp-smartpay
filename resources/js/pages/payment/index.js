import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DeletePayment, GetPayments } from '@/http/payment'
import { __ } from '@wordpress/i18n'
import { Container } from 'react-bootstrap'
import { DataTable } from '../../components/data-table'
import { createColumns } from './columns'

const { useEffect, useState, useCallback } = wp.element

export const PaymentList = () => {
	const [data, setData] = useState([])
	const [isLoading, setIsLoading] = useState(false)
	const [searchQuery, setSearchQuery] = useState('')
	const [paymentStatus, setPaymentStatus] = useState('')
	const [paymentType, setPaymentType] = useState('')
	const [sortBy, setSortBy] = useState('id:desc')
	const [pagination, setPagination] = useState({
		current_page: 1,
		per_page: 10,
		last_page: 1,
		total: 0,
		from: 0,
		to: 0
	})

	// Fetch payments from API
	const fetchPayments = useCallback(async (page = 1, perPage = 10, search = '', status = '', type = '', sortBy = 'id:desc') => {
		setIsLoading(true)

		try {
			const result = await GetPayments({ page, perPage, search, status, type, sortBy });

			// Extract data and pagination info
			const { data: paymentData = [], ...paginationData } = result;

			setData(paymentData)
			setPagination({
				current_page: paginationData.current_page,
				per_page: paginationData.per_page,
				last_page: paginationData.last_page,
				total: paginationData.total,
				from: paginationData.from,
				to: paginationData.to
			})
		} catch (error) {
			Swal.fire({
				icon: 'error',
				title: __('Error', 'smartpay'),
				text: __('Failed to load payments', 'smartpay'),
			})
		} finally {
			setIsLoading(false)
		}
	}, []);

	const deletePayment = async (paymentId) => {
		const deleted = await DeletePayment(paymentId);
		if (deleted) {
			fetchPayments(pagination.current_page, pagination.per_page, searchQuery, paymentStatus, paymentType, sortBy);
		}
	}

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

	// Create columns with deletePayment function
	const columns = createColumns(deletePayment)

	return (
		<>
			<div className="text-black bg-white border-bottom shadow-xs">
				<Container>
					<div className="d-flex align-items-center justify-content-between py-3">
						<div className='-mt-1.5'>
							<h2 className="text-slate-700! mb-1! mt-0! text-2xl! font-bold!">
								{__('Payments', 'smartpay')}
							</h2>
							<p className='text-slate-500 font-medium text-sm! m-0'>{__('Manage your payments here', 'smartpay')}</p>
						</div>
						<div className=''>
							<img className='w-full h-7' src={smartpay.logo} alt="SmartPay Logo"/>
						</div>
					</div>
				</Container>
			</div>

			<Container className="mt-4">
				<div className="bg-white p-4 rounded-lg shadow-md">
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
