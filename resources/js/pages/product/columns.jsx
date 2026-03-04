import { __ } from '@wordpress/i18n'
import { ExternalLink, SquarePen, Trash2 } from 'lucide-react'
import { Link } from 'react-router-dom'
export const createProductColumns = (deleteProduct) => {
	const { Button } = window.WPSmartPayUI
	return [
	{
		accessorKey: 'title',
		header: __('Title', 'smartpay'),
		enableSorting: false,
		cell: ({ row }) => {
			const product = row.original
			return (
				<div className="flex flex-col">
					<span className="font-medium text-gray-900">{product.title || __('Untitled', 'smartpay')}</span>
					<span className="text-xs text-gray-500">
						{__('ID', 'smartpay')}: {product.id}
					</span>
				</div>
			)
		},
	},
	{
		accessorKey: 'base_price',
		header: () => <div className="text-center">{__('Price', 'smartpay')}</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const product = row.original
			return (
				<div className="text-center text-sm text-gray-700 font-medium">
					{product.sale_price ? (
						<>
							<span className="line-through text-gray-400 mr-1">${product.base_price}</span>
							<span>${product.sale_price}</span>
						</>
					) : (
						<span>${product.base_price || '0.00'}</span>
					)}
				</div>
			)
		},
	},
	{
		accessorKey: 'created_at',
		header: () => <div className="text-center">{__('Date', 'smartpay')}</div>,
		enableSorting: false,
		cell: ({ row }) => {
			return (
				<div className="text-center text-sm text-gray-700">
					{row.getValue('created_at') || '—'}
				</div>
			)
		},
	},
	{
		id: 'actions',
		header: () => <div className="text-right mr-4!">{__('Actions', 'smartpay')}</div>,
		cell: ({ row }) => {
			const product = row.original

			return (
				<div className="flex items-center justify-end gap-2">
					{product?.extra?.product_preview_page_permalink && (
						<a href={product.extra.product_preview_page_permalink} target="_blank" rel="noopener noreferrer">
							<Button
								variant="outline"
								size="icon"
								title={__('Preview', 'smartpay')}
								className="hover:bg-gray-100 cursor-pointer"
							>
								<ExternalLink className="w-4 h-4 text-gray-700" />
							</Button>
						</a>
					)}
					<Link to={`/products/${product.id}/edit`}>
						<Button
							variant="outline"
							size="icon"
							title={__('Edit', 'smartpay')}
							className="hover:bg-gray-100 cursor-pointer"
						>
							<SquarePen className="w-4 h-4 text-gray-700" />
						</Button>
					</Link>
					<Button
						variant="outline"
						size="icon"
						title={__('Delete', 'smartpay')}
						onClick={() => deleteProduct(product.id)}
						className="hover:bg-red-50 cursor-pointer border-red-200!"
					>
						<Trash2 className="w-4 h-4 text-red-600" />
					</Button>
				</div>
			)
		},
	},
]
}
