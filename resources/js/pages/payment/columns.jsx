import { StatusBadge } from '@/components/status-badges'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { __ } from '@wordpress/i18n'
import { Box, Eye, FilePenLine, LinkIcon, Trash2 } from 'lucide-react'
import { Link } from 'react-router-dom'

export const createColumns = (deletePayment) => [
    {
		accessorKey: 'id',
		header: __('ID', 'smartpay'),
		enableSorting: false,
	},
	{
		accessorKey: 'email',
		header: __('Customer', 'smartpay'),
		enableSorting: false,
	},
	{
		accessorKey: 'type',
		header: () => <div className="text-center">{ __('Item & Type', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const type = row.getValue('type');
			const payment = row.original
			const productId = payment?.data?.product_id
			const formId = payment?.data?.form_id

			if (type === 'Product Purchase') {
				return (
					<div className='flex justify-center gap-2 items-center'>
						<Badge variant="secondary" className="bg-slate-100 min-w-20"><Box className='size-3'/> Product</Badge>
						<Badge variant="default" className="bg-slate-100 min-w-8">
							<Link
								to={`/products/${productId}/edit`}
								className="text-slate-800! font-bold flex items-center justify-center hover:underline"
							>
								<LinkIcon className='size-3.5'/>
							</Link>
						</Badge>
					</div>
				)
			} else if(type === 'Form Payment') {
				return (
					<div className='flex justify-center gap-2 items-center'>
						<Badge variant="secondary" className="bg-slate-100 min-w-20"><FilePenLine className='size-3'/> Form</Badge>
						<Badge variant="default" className="bg-slate-100 min-w-8">
							<span className='text-slate-700'>{`#${formId}`}</span>
						</Badge>
					</div>
				)
			}
		}
	},
	{
		accessorKey: 'date',
		header: () => <div className="text-center">{ __('Date & Time', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const dateStr = row.original.completed_at || row.original.created_at;
			const date = new Date(dateStr)
			return (<div className='text-center'>
				{date.toLocaleString('en-US', {
					year: 'numeric',
					month: 'short',
					day: 'numeric',
					hour: 'numeric',
					minute: 'numeric',
				})}
			</div>)
		},
	},
	{
		accessorKey: 'status',
		header: () => <div className="text-center">{ __('Status', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const status = row.getValue('status')
			return (
				<div className='flex items-center justify-center'>
					<StatusBadge status={status}/>
				</div>
			)
		},
	},
	{
		accessorKey: 'amount',
		header: __('Amount', 'smartpay'),
		cell: ({ row }) => {
			const amount = parseFloat( row.getValue('amount') || 0 )
			const formatted = new Intl.NumberFormat('en-US', {
				style: 'currency',
				currency: 'USD'
			}).format(amount)
			return (
                <div className='text-right pr-2'>
					{formatted}
				</div>
			)
		},
	},
	{
		id: 'actions',
		header: () => <div className="text-center">{ __('Actions', 'smartpay') }</div>,
		cell: ({ row }) => {
			const payment = row.original

			return (
				<div className="flex items-center justify-end gap-2">
					<Link to={`/payments/${payment.id}/edit`}>
						<Button
							variant="outline"
							size="icon"
							title={__('View', 'smartpay')}
							className="hover:bg-gray-100"
							>
							<Eye className="w-4 h-4 text-gray-700" />
						</Button>
					</Link>
					<Button
						variant="outline"
						size="icon"
						title={__('Delete', 'smartpay')}
						onClick={() => deletePayment(payment.id)}
						className="hover:bg-red-50"
					>
						<Trash2 className="w-4 h-4 text-red-600" />
					</Button>
				</div>
			)
		},
	},
]
