import { DataTable } from '@/components/data-table'
import Header from '@/components/Header'
import { __ } from '@wordpress/i18n'
import { Container } from 'react-bootstrap'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { DeleteCustomer, GetCustomers } from '../../http/customer'
import { createColumns } from './columns'
const { useEffect, useState, useCallback } = wp.element

export const CustomerList = () => {
	const [data, setData] = useState([])
	const [isLoading, setIsLoading] = useState(false)
	const [searchQuery, setSearchQuery] = useState('')
	const [pagination, setPagination] = useState({
		current_page: 1,
		per_page: 10,
		last_page: 1,
		total: 0,
		from: 0,
		to: 0
	})

	const fetchCustomers = useCallback(async (page = 1, perPage = 10, search = '') => {
		setIsLoading(true)

		try {
			const result = await GetCustomers({ page, perPage, search });

			// Extract data and pagination info
			const { data: customerData = [], ...paginationData } = result;

			setData(customerData)
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

	useEffect(() => {
		fetchCustomers(1, pagination.per_page, searchQuery)
	}, [fetchCustomers, searchQuery, pagination.per_page])

	const handlePaginationChange = useCallback(({ page, per_page }) => {
		fetchCustomers(page, per_page, searchQuery)
	}, [fetchCustomers, searchQuery])

	const handleSearchChange = (search) => {
		setSearchQuery(search)
	}

    const deleteCustomer = async (customerId) => {
        const deleted = await DeleteCustomer(customerId);
		if (deleted) {
			fetchCustomers(pagination.current_page, pagination.per_page, searchQuery);
		}
    }

	// Create columns with deleteCustomer function
	const columns = createColumns(deleteCustomer)

    return (
        <>
			<Header
				title={__('Customers', 'smartpay')}
				subtitle={__('Manage your customers here', 'smartpay')}
			/>

			<Container className="mt-4">
				<div className="bg-white p-4 rounded-lg shadow-md">
					<DataTable
						columns={columns}
						data={data}
						pagination={pagination}
						onPaginationChange={handlePaginationChange}
						onSearchChange={handleSearchChange}
						isLoading={isLoading}
						searchPlaceholder='Search by Email...'
					/>
				</div>
			</Container>
        </>
    )
}
