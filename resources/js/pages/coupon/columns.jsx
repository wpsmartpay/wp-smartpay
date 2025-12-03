import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { __ } from '@wordpress/i18n'
import { BadgeCheck, Percent, Pin, SquarePen, Timer, Trash2 } from 'lucide-react'
import { ShieldOff } from 'react-feather'
import { Link } from 'react-router-dom'

export const createColumns = (deletePayment) => [
	{
		accessorKey: 'title',
		header: __('COUPON CODE', 'smartpay'),
		enableSorting: false,
		cell: ({ row }) => {
			const title = row.getValue('title');
			return (
				<div className='font-medium uppercase text-slate-800'>
					{title}
				</div>
			)
		}
	},
	{
		accessorKey: 'discount_type',
		header: () => <div className="text-center">{ __('Type', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const type = row.getValue('discount_type');

			if (type === 'percent') {
				return (
					<div className='flex justify-center gap-2 items-center'>
						<Badge variant="secondary" className="bg-purple-50 text-purple-600 min-w-20"><Percent className='size-3'/> Percent</Badge>
					</div>
				)
			} else if(type === 'fixed') {
				return (
					<div className='flex justify-center gap-2 items-center'>
						<Badge variant="secondary" className="bg-blue-50 text-blue-600 min-w-20"><Pin className='size-3'/> Fixed</Badge>
					</div>
				)
			}
		}
	},
	{
		accessorKey: 'discount_amount',
		header: () => <div className="text-center">{ __('Amount', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const amount = row.getValue('discount_amount');
			const type = row.original.discount_type;

			return (
				<div className='text-center text-slate-800 font-medium'>
					{type === 'percent' ? `${amount}%` : amount}
				</div>
			)
		}
	},
	{
		accessorKey: 'expiry_date',
		header: () => <div className="text-center">{ __('Expire At', 'smartpay') }</div>,
		enableSorting: false,
		cell: ({ row }) => {
			const dateStr = row.original.expiry_date;
			const date = new Date(dateStr)
			const now = new Date();

			if (dateStr === null) {
				return <div className='text-center'><Badge variant="secondary" className="bg-green-50 text-green-700 bordering-green-200 min-w-20"><BadgeCheck className='size-3'/> { __('Never', 'smartpay') }</Badge></div>
			}
			if (date < now) {
				return <div className='text-center'><Badge variant="secondary" className="bg-red-50 text-red-700 bordering-red-200 min-w-20"><ShieldOff className='size-3'/> { __('Expired', 'smartpay') }</Badge></div>
			}
			return (<div className='text-center'>
				<Badge variant="secondary" className="bg-slate-50 text-slate-700 bordering-slate-200 min-w-20"><Timer className='size-3'/>
					{date.toLocaleString('en-US', {
						year: 'numeric',
						month: 'short',
						day: 'numeric',
						hour: 'numeric',
						minute: 'numeric',
					})}
				</Badge>
			</div>)
		},
	},
	{
		id: 'actions',
		header: () => <div className="text-right mr-4">{ __('Actions', 'smartpay') }</div>,
		cell: ({ row }) => {
			const coupon = row.original
			return (
				<div className="flex items-center justify-end gap-2">
					<Link className="btn-sm p-0 mr-2 text-decoration-none" to={`/coupons/${coupon.id}/edit`}>
                        <Button
							variant="outline"
							size="icon"
							title={__('Edit', 'smartpay')}
							className="hover:bg-gray-100"
						>
							<SquarePen className="w-4 h-4 text-gray-700" />
						</Button>
                    </Link>
					<Button
						variant="outline"
						size="icon"
						title={__('Delete', 'smartpay')}
						onClick={() => deletePayment(coupon.id)}
						className="hover:bg-red-50"
					>
						<Trash2 className="w-4 h-4 text-red-600" />
					</Button>
				</div>
			)
		},
	},
]
