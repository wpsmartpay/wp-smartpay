import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'
import { Link } from 'react-router-dom'
import { DeleteProduct, GetProducts } from '../../http/product'
import { createProductColumns } from './columns'
import { createHooks } from '@wordpress/hooks'
const { useState, useEffect, useCallback } = wp.element

window.SMARTPAY_PRODUCT_HOOKS = createHooks()

export const ProductList = () => {
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

	const fetchProducts = useCallback(async (page = 1, perPage = 10, search = '', sortBy = 'id:desc') => {
		setIsLoading(true)

		try {
			const result = await GetProducts({ page, perPage, search, sortBy })
			const { data: productData = [], ...paginationData } = result

			setData(productData)
			setPagination({
				current_page: paginationData.current_page,
				per_page: paginationData.per_page,
				last_page: paginationData.last_page,
				total: paginationData.total,
				from: paginationData.from,
				to: paginationData.to,
			})
		} catch (error) {
			console.error('Failed to load products', error)
		} finally {
			setIsLoading(false)
		}
	}, [])

	useEffect(() => {
		fetchProducts(1, pagination.per_page, searchQuery, sortBy)
	}, [fetchProducts, searchQuery, pagination.per_page, sortBy])

	const handlePaginationChange = useCallback(({ page, per_page }) => {
		fetchProducts(page, per_page, searchQuery, sortBy)
	}, [fetchProducts, searchQuery, sortBy])

	const handleSearchChange = (search) => {
		setSearchQuery(search)
	}

	const deleteProduct = async (productId) => {
		const deleted = await DeleteProduct(productId)
		if (deleted) {
			fetchProducts(pagination.current_page, pagination.per_page, searchQuery, sortBy)
		}
	}

	const columns = createProductColumns(deleteProduct)

	return (
		<>
			<Header
				title={__('Products', 'smartpay')}
				subtitle={__('Manage your products here', 'smartpay')}
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
						searchPlaceholder={__('Search by product name', 'smartpay')}
						enableActions={true}
						actions={[
							<Link to="/products/create" key="create-product-link">
								<Button
									key="create-product"
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
