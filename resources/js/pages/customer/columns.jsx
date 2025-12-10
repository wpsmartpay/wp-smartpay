import { __ } from '@wordpress/i18n'
import { Eye, Trash2 } from 'lucide-react'
import { Link } from 'react-router-dom'
const { Button } = window.WPSmartPayUI;

export const createColumns = (deleteCustomer) => [
	{
		accessorKey: 'id',
		header: __('ID', 'smartpay'),
		enableSorting: false,
	},
	{
		accessorKey: 'first_name',
		header: __('Name', 'smartpay'),
		enableSorting: false,
		cell: ({ row }) => {
			const customer = row.original
			const fullName = `${customer.first_name} ${customer.last_name}`.trim() || __('N/A', 'smartpay')
			return fullName
		}
	},
	{
		accessorKey: 'email',
		header: __('Email', 'smartpay'),
		enableSorting: false,
	},
	{
		accessorKey: 'created_at',
		header: () => <div className="text-center">{ __('Member Since', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const dateStr = row.original.created_at;
			const date = new Date(dateStr)
			return (<div className='text-center'>
				{date.toLocaleString('en-US', {
					year: 'numeric',
					month: 'short',
					day: 'numeric',
				})}
			</div>)
		},
	},
	{
		id: 'actions',
		header: () => <div className="text-right mr-3">{ __('Actions', 'smartpay') }</div>,
		cell: ({ row }) => {
			const customer = row.original

			return (
				<div className="flex items-center justify-end gap-2">
					<Link to={`/customers/${customer.id}`}>
						<Button
							variant="outline"
							size="icon"
							title={__('Details', 'smartpay')}
							className="hover:bg-gray-100"
						>
							<Eye className="w-4 h-4 text-gray-700" />
						</Button>
					</Link>
					<Button
						variant="outline"
						size="icon"
						title={__('Delete', 'smartpay')}
						onClick={() => deleteCustomer(customer.id)}
						className="hover:bg-red-50"
					>
						<Trash2 className="w-4 h-4 text-red-600" />
					</Button>
				</div>
			)
		},
	},
]
