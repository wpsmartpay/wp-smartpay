import { StatusBadge } from '@/components/status-badges'
import { Button } from '@/components/ui/button'
import { __ } from '@wordpress/i18n'
import { Eye, Trash2 } from 'lucide-react'
import { Link } from 'react-router-dom'

export const createColumns = (deletePayment) => [
    {
        accessorKey: 'id',
        header: __('ID', 'smartpay'),
    },
    {
        accessorKey: 'email',
        header: __('Customer', 'smartpay'),
    },
    {
        accessorKey: 'type',
        header: __('Type', 'smartpay'),
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
                <span>
                    {/* {smartpay.options.currencySymbol} {amount} */}
					{formatted}
                </span>
            )
        },
    },
    {
        accessorKey: 'date',
        header: __('Date', 'smartpay'),
        cell: ({ row }) => {
			const dateStr = row.original.completed_at || row.original.created_at;
			const date = new Date(dateStr)
			return (<span>
				{date.toLocaleString('en-US', {
					year: 'numeric',
					month: 'short',
					day: 'numeric',
					hour: 'numeric',
					minute: 'numeric',
				})}
			</span>)
            // return row.original.completed_at || row.original.created_at
        },
    },
    {
        accessorKey: 'status',
        header: __('Status', 'smartpay'),
        cell: ({ row }) => {
            const status = row.getValue('status')
            return (
				<div className='flex justify-center items-center'>
					<StatusBadge status={status}/>
				</div>
            )
        },
    },
    {
        id: 'actions',
        header: __('Actions', 'smartpay'),
        cell: ({ row }) => {
            const payment = row.original

            return (
                <div className="flex items-center justify-center gap-2">
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
