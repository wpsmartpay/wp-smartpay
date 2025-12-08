import StatCard from '@/components/stat-card';
import { __ } from '@wordpress/i18n';
import { CircleUser } from 'lucide-react';

export default function CustomerStats({ customer }){
	return (
		<div className="bg-white p-4 rounded-lg shadow-sm">
			<div className="flex justify-between items-center">
				<div className="flex gap-4 items-center">
					<CircleUser size={60} className='text-gray-500' />
					<div className="">
							<h2 className="m-0 mb-1">
								{customer?.full_name}
							</h2>
							<p className="m-0 text-muted">
								{customer?.email}
							</p>
					</div>
				</div>
				<div className="">
					<h3 className="my-0 mb-2 text-right">{`#${customer?.id}`}</h3>
					<p className="m-0">
						{__('Customer since', 'smartpay')}{' '}
						<strong>
							{new Date(customer?.created_at).toLocaleString('en-US', {
								year: 'numeric',
								month: 'short',
								day: 'numeric',
							})}
						</strong>
					</p>
				</div>
			</div>

			<div className="flex flex-wrap gap-6 mt-4">
				<StatCard title={__('Total Payments', 'smartpay')} value={customer?.payments?.total} type="info" />
				<StatCard title={__('Completed Payments', 'smartpay')} value={customer?.paymentStats?.completed} type="success" />
				<StatCard title={__('Pending Payments', 'smartpay')} value={customer?.paymentStats?.pending} type="warning" />
				<StatCard title={__('Refunded Payments', 'smartpay')} value={customer?.paymentStats?.refunded} type="danger" />
			</div>
		</div>
	);
}
