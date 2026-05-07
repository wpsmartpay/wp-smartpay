import { __ } from '@wordpress/i18n';
import { Eye, RefreshCw, ScanSearch, Trash2 } from 'lucide-react';
import { Link } from 'react-router-dom';
const { Badge, Button, StatusBadge } = window.WPSmartPayUI;

const isSubscription = ( billingType ) =>
	typeof billingType === 'string' && billingType.toLowerCase() === 'subscription'

const periodLabel = ( period ) => {
	const map = { day: 'day', week: 'wk', month: 'mo', year: 'yr' }
	return map[ ( period || '' ).toLowerCase() ] || period || ''
}

export const createColumns = (deletePayment, onViewPayment) => [
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
		header: () => <div className="text-center">{ __('Type', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const payment      = row.original
			const billingType  = payment?.data?.billing_type

			if ( isSubscription( billingType ) ) {
				return (
					<div className='flex justify-center'>
						<Badge variant="secondary" style={{ background: '#ede9fe', color: '#6d28d9', display: 'inline-flex', alignItems: 'center', gap: 4 }}>
							<RefreshCw className='size-3' /> { __( 'Subscription', 'smartpay' ) }
						</Badge>
					</div>
				)
			}
			return (
				<div className='flex justify-center'>
					<Badge variant="secondary" style={{ background: '#f0fdf4', color: '#166534', display: 'inline-flex', alignItems: 'center', gap: 4 }}>
						{ __( 'One-time', 'smartpay' ) }
					</Badge>
				</div>
			)
		}
	},
	{
		accessorKey: 'source_name',
		header: () => <div className="text-center">{ __('Product / Form', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const payment = row.original
			const type    = payment.type
			const data    = payment.data || {}

			if (type === 'Product Purchase') {
				return (
					<div className='flex justify-center items-center gap-1.5'>
						<span className='text-sm text-gray-700'>
							#{ data?.product_id || '—' }
						</span>
						<span className='inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200 capitalize'>
							product
						</span>
					</div>
				)
			}
			if (type === 'Form Payment') {
				return (
					<div className='flex justify-center items-center gap-1.5'>
						<span className='text-sm text-gray-700'>
							#{ data?.form_id || '—' }
						</span>
						<span className='inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200 capitalize'>
							form
						</span>
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
			const payment     = row.original
			const amount      = parseFloat( row.getValue('amount') || 0 )
			const currency    = payment?.currency || 'USD'
			const billingType = payment?.data?.billing_type
			const period      = periodLabel( payment?.data?.billing_period )

			const formatted = new Intl.NumberFormat('en-US', {
				style: 'currency',
				currency: currency,
			}).format(amount)

			return (
				<div className='text-right pr-2'>
					<span>{ formatted }</span>
					{ isSubscription( billingType ) && period && (
						<span style={{ color: '#6b7280', fontSize: 11, marginLeft: 3 }}>
							/ { period }
						</span>
					) }
				</div>
			)
		},
	},
	{
		id: 'actions',
		header: () => <div className="text-right mr-3">{ __('Actions', 'smartpay') }</div>,
		cell: ({ row }) => {
			const payment = row.original

			return (
				<div className="flex items-center justify-end gap-2">
					<Link to={`/payments/${payment.id}`}>
						<Button
							variant="outline"
							size="icon"
							title={__('View Details', 'smartpay')}
							className="hover:bg-gray-100 cursor-pointer"
						>
							<Eye className="w-4 h-4 text-gray-700" />
						</Button>
					</Link>
					<Button
						variant="outline"
						size="icon"
						title={__('Quick View', 'smartpay')}
						onClick={() => onViewPayment(payment.id)}
						className="hover:bg-gray-100 cursor-pointer"
					>
						<ScanSearch className="w-4 h-4 text-gray-700" />
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
