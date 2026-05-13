import { __ } from '@wordpress/i18n'
import { Header } from './header'

const LockedFeaturePage = ({ title, subtitle, excerpt, features = [] }) => {
    const upgradeUrl = 'https://wpsmartpay.com/pricing'

    return (
        <>
            <Header title={title} subtitle={subtitle} />
            <div className="sp-content-wide">
                <div
                    className="bg-white border border-border rounded-lg flex flex-col items-center text-center"
                    style={{ maxWidth: '480px', margin: '2rem auto', padding: '2.5rem 2rem' }}
                >
                    <div style={{ fontSize: '2.5rem', marginBottom: '12px', lineHeight: 1 }}>🔒</div>
                    <h3 style={{ margin: '0 0 6px', fontSize: '1rem', fontWeight: 600 }}>
                        {title}
                        <span
                            className="sp-badge sp-badge--neutral"
                            style={{ marginLeft: '8px', verticalAlign: 'middle' }}
                        >
                            {__('Pro', 'smartpay')}
                        </span>
                    </h3>
                    {excerpt && (
                        <p className="text-sm text-muted-foreground" style={{ margin: '0 0 20px' }}>
                            {excerpt}
                        </p>
                    )}
                    {features.length > 0 && (
                        <ul
                            style={{
                                textAlign: 'left',
                                width: '100%',
                                margin: '0 0 24px',
                                padding: 0,
                                listStyle: 'none',
                            }}
                        >
                            {features.map((f, i) => (
                                <li
                                    key={i}
                                    className="text-sm"
                                    style={{ display: 'flex', gap: '8px', marginBottom: '8px' }}
                                >
                                    <span style={{ color: '#16a34a', fontWeight: 700, flexShrink: 0 }}>✓</span>
                                    <span>{f}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                    <a
                        href={upgradeUrl}
                        target="_blank"
                        rel="noreferrer"
                        className="sp-btn sp-btn--primary"
                    >
                        {__('Upgrade to SmartPay Pro', 'smartpay')} →
                    </a>
                </div>
            </div>
        </>
    )
}

export const SubscriptionsLockedPage = () => (
    <LockedFeaturePage
        title={__('Subscriptions', 'smartpay')}
        subtitle={__('Manage recurring payments and billing cycles', 'smartpay')}
        excerpt={__('Create subscription plans, manage billing cycles, and let customers self-manage — all in one place.', 'smartpay')}
        features={[
            __('Recurring payment plans with flexible billing', 'smartpay'),
            __('Free trials and setup fees', 'smartpay'),
            __('Customer self-service cancellation portal', 'smartpay'),
            __('Dunning management and retry logic', 'smartpay'),
        ]}
    />
)

export const ReportsLockedPage = () => (
    <LockedFeaturePage
        title={__('Reports', 'smartpay')}
        subtitle={__('Insights and analytics for your business', 'smartpay')}
        excerpt={__('Track revenue, monitor growth, and analyze customer behavior with detailed, filterable reports.', 'smartpay')}
        features={[
            __('Revenue and sales over time', 'smartpay'),
            __('Subscription metrics and churn', 'smartpay'),
            __('Customer lifetime value', 'smartpay'),
            __('Export reports to CSV', 'smartpay'),
        ]}
    />
)
