import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'
import { Link } from 'react-router-dom'
import { Delete, GetForms } from '../http/form'
import { createFormColumns } from './columns'
import { createHooks } from '@wordpress/hooks'
const { useState, useEffect, useCallback } = wp.element

window.SMARTPAY_FORM_HOOKS = createHooks()

export const FormList = () => {
	const { Header, DataTable, Button } = window.WPSmartPayUI
	const [data, setData] = useState([])
	const [isLoading, setIsLoading] = useState(false)
	const [searchQuery, setSearchQuery] = useState('')
	const [sortBy, setSortBy] = useState('id:desc')
	const [pagination, setPagination] = useState({
		current_page: 1,
		per_page: 10,
		last_page: 1,
		total: 0,
		from: 0,
		to: 0,
	})

	const fetchForms = useCallback(async (page = 1, perPage = 10, search = '', sortBy = 'id:desc') => {
		setIsLoading(true)

		try {
			const result = await GetForms({ page, perPage, search, sortBy })
			const { data: formData = [], ...paginationData } = result

			setData(formData)
			setPagination({
				current_page: paginationData.current_page,
				per_page: paginationData.per_page,
				last_page: paginationData.last_page,
				total: paginationData.total,
				from: paginationData.from,
				to: paginationData.to,
			})
		} catch (error) {
			console.error('Failed to load forms', error)
		} finally {
			setIsLoading(false)
		}
	}, [])

	useEffect(() => {
		fetchForms(1, pagination.per_page, searchQuery, sortBy)
	}, [fetchForms, searchQuery, pagination.per_page, sortBy])

	const handlePaginationChange = useCallback(({ page, per_page }) => {
		fetchForms(page, per_page, searchQuery, sortBy)
	}, [fetchForms, searchQuery, sortBy])

	const handleSearchChange = (search) => {
		setSearchQuery(search)
	}

	const deleteForm = async (formId) => {
		const deleted = await Delete(formId)
		if (deleted) {
			fetchForms(pagination.current_page, pagination.per_page, searchQuery, sortBy)
		}
	}

	const columns = createFormColumns(deleteForm)

	return (
		<>
			<Header
				title={__('Forms', 'smartpay')}
				subtitle={__('Manage your forms here', 'smartpay')}
			/>

			<div className="p-4 max-w-7xl mx-auto">
				<div className="bg-white p-4 rounded-md shadow-md">
					<DataTable
						columns={columns}
						data={data}
						pagination={pagination}
						onPaginationChange={handlePaginationChange}
						onSearchChange={handleSearchChange}
						isLoading={isLoading}
						searchPlaceholder={__('Search by form name', 'smartpay')}
						enableActions={true}
						actions={[
							<Link to="create" key="create-form-link">
								<Button
									key="create-form"
									variant="default"
									title={__('Create', 'smartpay')}
								>
									<Plus className="w-4 h-4 text-white" />
									<span>{__('Add New', 'smartpay')}</span>
								</Button>
							</Link>
						]}
					/>
				</div>
			</div>
		</>
	)
}
