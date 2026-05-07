import { __ } from '@wordpress/i18n';
import { Box, Eye, FilePenLine, LinkIcon, Trash2 } from 'lucide-react';
import { Link } from 'react-router-dom';
const { StatusBadge, Badge, Button } = window.WPSmartPayUI;

export const createPaymentColumns = (deletePayment, onViewPayment) => [
    {
		accessorKey: 'id',
		header: __('ID', 'smartpay'),
		enableSorting: false,
	},
	{
		accessorKey: 'type',
		header: () => <div className="text-center">{ __('Item & Type', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const payment = row.original
			const type    = row.getValue('type')
			const data    = payment.data || {}

			if (type === 'Product Purchase') {
				return (
					<div className='flex justify-center gap-2 items-center'>
						<Badge variant="secondary" className="bg-slate-100 min-w-20"><Box className='size-3'/> {__( 'Product', 'smartpay' )}</Badge>
						<span className='text-sm font-medium text-gray-700'>#{ data?.product_id || '—' }</span>
					</div>
				)
			}
			if (type === 'Form Payment') {
				return (
					<div className='flex justify-center gap-2 items-center'>
						<Badge variant="secondary" className="bg-slate-100 min-w-20"><FilePenLine className='size-3'/> {__( 'Form', 'smartpay' )}</Badge>
						<span className='text-sm font-medium text-gray-700'>#{ data?.form_id || '—' }</span>
					</div>
				)
			}
			return <span className="text-muted-foreground">—</span>
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
					<Button
						variant="outline"
						size="icon"
						title={__('View', 'smartpay')}
						onClick={() => onViewPayment(payment.id)}
						className="hover:bg-gray-100 cursor-pointer"
					>
						<Eye className="w-4 h-4 text-gray-700" />
					</Button>
					<Button
						variant="outline"
						size="icon"
						title={__('Delete', 'smartpay')}
						onClick={() => deletePayment(payment.id)}
						className="hover:bg-red-50 cursor-pointer border-red-200!"
					>
						<Trash2 className="w-4 h-4 text-red-600" />
					</Button>
				</div>
			)
		},
	},
]
