import { __ } from '@wordpress/i18n';
import { CircleUser } from 'lucide-react';
const { StatCard, Card, CardContent } = window.WPSmartPayUI;

export default function CustomerStats({ customer, paymentStats }){
	return (
		<Card>
			<CardContent>
				<div className="flex justify-between items-center">
					<div className="flex gap-4 items-center">
						<CircleUser size={60} className='text-muted-foreground' />
						<div>
							<h2 className="m-0 mb-1 text-card-foreground">
								{customer?.full_name}
							</h2>
							<p className="m-0 text-muted-foreground">
								{customer?.email}
							</p>
						</div>
					</div>
					<div>
						<h3 className="my-0 mb-2 text-right text-muted-foreground">{`#${customer?.id}`}</h3>
						<p className="m-0 text-sm text-muted-foreground">
							{__('Member since', 'smartpay')}{' '}
							<strong className="text-card-foreground">
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
					<StatCard title={__('Total Payments', 'smartpay')} value={paymentStats.total} type="info" />
					<StatCard title={__('Completed Payments', 'smartpay')} value={paymentStats.completed} type="success" />
					<StatCard title={__('Pending Payments', 'smartpay')} value={paymentStats.pending} type="warning" />
					<StatCard title={__('Refunded Payments', 'smartpay')} value={paymentStats.refunded} type="danger" />
				</div>
			</CardContent>
		</Card>
	);
}
