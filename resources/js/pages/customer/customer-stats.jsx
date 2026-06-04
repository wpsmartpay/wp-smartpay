import { __ } from '@wordpress/i18n';
import { CircleUser } from 'lucide-react';

const StatItem = ({ title, value, color }) => (
	<div style={{ background: 'var(--sp-surface-muted)', border: '1px solid var(--sp-border)', borderRadius: 'var(--sp-radius-sm)', padding: '10px 16px', minWidth: 110 }}>
		<div style={{ fontSize: 11, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--sp-text-subtle)', marginBottom: 4 }}>{title}</div>
		<div style={{ fontSize: 22, fontWeight: 700, color: color || 'var(--sp-text)' }}>{value ?? 0}</div>
	</div>
)

export default function CustomerStats({ customer, paymentStats }) {
	return (
		<div className="sp-detail-card">
			<div className="sp-detail-card__body">
				<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 12 }}>
					<div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
						<CircleUser size={52} style={{ color: 'var(--sp-text-subtle)', flexShrink: 0 }} />
						<div>
							<h2 style={{ margin: '0 0 3px', fontSize: 18, fontWeight: 700, color: 'var(--sp-text)' }}>{customer?.full_name}</h2>
							<p style={{ margin: 0, fontSize: 13, color: 'var(--sp-text-muted)' }}>{customer?.email}</p>
						</div>
					</div>
					<div style={{ textAlign: 'right' }}>
						<div style={{ fontSize: 13, fontWeight: 600, color: 'var(--sp-text-subtle)', marginBottom: 4 }}>#{customer?.id}</div>
						<p style={{ margin: 0, fontSize: '12.5px', color: 'var(--sp-text-muted)' }}>
							{__('Member since', 'smartpay')}{' '}
							<strong style={{ color: 'var(--sp-text)' }}>{new Date(customer?.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</strong>
						</p>
					</div>
				</div>
				<div style={{ display: 'flex', flexWrap: 'wrap', gap: 10, marginTop: 16, paddingTop: 16, borderTop: '1px solid var(--sp-border)' }}>
					<StatItem title={__('Total Payments', 'smartpay')} value={paymentStats.total} />
					<StatItem title={__('Completed', 'smartpay')} value={paymentStats.completed} color="var(--sp-brand)" />
					<StatItem title={__('Pending', 'smartpay')} value={paymentStats.pending} />
					<StatItem title={__('Refunded', 'smartpay')} value={paymentStats.refunded} />
				</div>
			</div>
		</div>
	)
}
