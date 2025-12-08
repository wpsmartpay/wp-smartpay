import Header from '@/components/Header'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DeletePayment, GetPayments } from '@/http/payment'
import { __ } from '@wordpress/i18n'
import { Container } from 'react-bootstrap'
import { useParams } from 'react-router-dom'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DataTable } from '../../components/data-table'
import { Loading } from '../../components/Loading'
import { PaymentDetailsDialog } from '../payment/PaymentDetailsDialog'
import CustomerStats from './customer-stats'
import { createPaymentColumns } from './payment-columns'

const { useEffect, useState, useCallback } = wp.element
const { useSelect } = wp.data

export const ShowCustomer = () => {
    const { customerId } = useParams()
    const [customer, setCustomer] = useState({})
    const [isLoading, setIsLoading] = useState(true)
    const [payments, setPayments] = useState([])
    const [isPaymentsLoading, setIsPaymentsLoading] = useState(false)
    const [searchQuery, setSearchQuery] = useState('')
    const [paymentStatus, setPaymentStatus] = useState('')
    const [paymentType, setPaymentType] = useState('')
    const [sortBy, setSortBy] = useState('id:desc')
    const [selectedPaymentId, setSelectedPaymentId] = useState(null)
    const [isDialogOpen, setIsDialogOpen] = useState(false)
    const [pagination, setPagination] = useState({
		current_page: 1,
		per_page: 5,
		last_page: 1,
		total: 0,
		from: 0,
		to: 0
	})

    const customerData = useSelect(
        (select) => select('smartpay/customers').getCustomer(customerId),
        [customerId]
    )

    useEffect(() => {
		console.log('Customer Data:', customerData);
		setCustomer(customerData)
		setIsLoading(false)
    }, [customerData])

	// Fetch payments from API
	const fetchPayments = useCallback(async (page = 1, perPage = 5, search = '', status = '', type = '', sortBy = 'id:desc') => {
		setIsPaymentsLoading(true)

		try {
			const result = await GetPayments({
				page,
				perPage,
				search,
				status,
				type,
				customerId: customerId,
				sortBy
			});

			// Extract data and pagination info
			const { data: paymentData = [], ...paginationData } = result;

			setPayments(paymentData)
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
			setIsPaymentsLoading(false)
		}
	}, [customerId]);

	const deletePayment = async (paymentId) => {
		const deleted = await DeletePayment(paymentId);
		if (deleted) {
			fetchPayments(pagination.current_page, pagination.per_page, searchQuery, paymentStatus, paymentType, sortBy);
		}
	}

	const handleViewPayment = (paymentId) => {
		setSelectedPaymentId(paymentId)
		setIsDialogOpen(true)
	}

	const handleDialogClose = (open) => {
		setIsDialogOpen(open)
		if (!open) {
			setSelectedPaymentId(null)
			fetchPayments(pagination.current_page, pagination.per_page, searchQuery, paymentStatus, paymentType, sortBy)
		}
	}

	useEffect(() => {
		if (customerId) {
			fetchPayments(1, pagination.per_page, searchQuery, paymentStatus, paymentType, sortBy)
		}
	}, [customerId, fetchPayments, searchQuery, paymentStatus, paymentType, pagination.per_page, sortBy])

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

	// Create columns with deletePayment and handleViewPayment functions
	const columns = createPaymentColumns(deletePayment, handleViewPayment)

    return (
        <>
			<Header
				title={__('Customer Details', 'smartpay')}
				subtitle={__('View and manage customer information', 'smartpay')}
			/>

            <Container className="mt-4">
                {isLoading ? (
                    <Loading />
                ) : (
					<>
						<CustomerStats customer={customer} />

						<div className="bg-white p-4 rounded-lg shadow-md mt-4">
							<h3 className="m-0! text-xl!">{__('Recent Payments', 'smartpay')}</h3>
							<DataTable
								columns={columns}
								data={payments}
								pagination={pagination}
								onPaginationChange={handlePaginationChange}
								onSearchChange={handleSearchChange}
								enableSorting={true}
								onSortChange={handleSort}
								isLoading={isPaymentsLoading}
								searchPlaceholder='Search by Transaction ID'
								enableFilters={true}
								filters={[
									<Select key="status-filter" onValueChange={handleStatusFilter}>
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
									<Select key="type-filter" onValueChange={handleTypeFilter}>
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
					</>
                )}
            </Container>
			<PaymentDetailsDialog
				paymentId={selectedPaymentId}
				open={isDialogOpen}
				onOpenChange={handleDialogClose}
			/>
        </>
    )
}
