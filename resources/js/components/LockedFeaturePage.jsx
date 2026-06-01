import { __ } from '@wordpress/i18n'
import { Header } from './header'

const LockIcon = () => (
    <div
        style={{
            width: 56,
            height: 56,
            borderRadius: 12,
            border: '2px solid #f97316',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            margin: '0 auto 20px',
        }}
    >
        <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#f97316"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
    </div>
)

const TablePlaceholder = () => (
    <div
        style={{
            background: 'white',
            borderRadius: 8,
            border: '1px solid #e5e7eb',
            overflow: 'hidden',
        }}
    >
        <div
            style={{
                padding: '12px 16px',
                borderBottom: '1px solid #e5e7eb',
                display: 'flex',
                gap: 8,
                alignItems: 'center',
            }}
        >
            <div style={{ width: 180, height: 32, background: '#f3f4f6', borderRadius: 4 }} />
            <div style={{ width: 120, height: 32, background: '#f3f4f6', borderRadius: 4 }} />
            <div style={{ marginLeft: 'auto', width: 100, height: 32, background: '#e5e7eb', borderRadius: 4 }} />
        </div>
        <div
            style={{
                display: 'grid',
                gridTemplateColumns: '2fr 1.5fr 1fr 1fr 1fr',
                padding: '10px 16px',
                background: '#f9fafb',
                gap: 8,
            }}
        >
            {[70, 75, 50, 60, 65].map((w, i) => (
                <div key={i} style={{ height: 13, background: '#d1d5db', borderRadius: 2, width: `${w}%` }} />
            ))}
        </div>
        {[62, 78, 55, 80, 68, 72].map((w, i) => (
            <div
                key={i}
                style={{
                    display: 'grid',
                    gridTemplateColumns: '2fr 1.5fr 1fr 1fr 1fr',
                    padding: '13px 16px',
                    borderBottom: '1px solid #f3f4f6',
                    gap: 8,
                    alignItems: 'center',
                }}
            >
                <div style={{ height: 12, background: '#e5e7eb', borderRadius: 2, width: `${w}%` }} />
                <div style={{ height: 12, background: '#e5e7eb', borderRadius: 2, width: '82%' }} />
                <div style={{ height: 12, background: '#d1fae5', borderRadius: 2, width: '55%' }} />
                <div
                    style={{
                        height: 20,
                        background: i % 3 === 0 ? '#fef3c7' : '#d1fae5',
                        borderRadius: 10,
                        width: '58%',
                    }}
                />
                <div style={{ height: 12, background: '#e5e7eb', borderRadius: 2, width: '70%' }} />
            </div>
        ))}
    </div>
)

const LockedFeaturePage = ({ title, subtitle, excerpt }) => {
    const proData = window.smartpayProData
    const isInstalled = proData?.isInstalled ?? false
    const licenseUrl =
        (window.smartpay?.adminUrl ?? '') + '?page=smartpay-setting&tab=licenses'
    const upgradeUrl = 'https://wpsmartpay.com/pricing'

    const ctaUrl = isInstalled ? licenseUrl : upgradeUrl
    const ctaLabel = isInstalled
        ? __('Activate Your License', 'smartpay')
        : __('Upgrade to WPSmartPay Pro', 'smartpay')
    const modalTitle = isInstalled
        ? __('License activation required', 'smartpay')
        : /* translators: %s feature name */ __('Unlock', 'smartpay') + ' ' + title
    const modalDesc = isInstalled
        ? __(
              'Your WPSmartPay Pro license needs to be activated to access this feature.',
              'smartpay'
          )
        : excerpt

    return (
        <>
            <Header title={title} subtitle={subtitle} />
            <div className="sp-content-wide" style={{ position: 'relative' }}>
                {/* Blurred placeholder */}
                <div
                    style={{
                        filter: 'blur(5px)',
                        pointerEvents: 'none',
                        userSelect: 'none',
                        opacity: 0.65,
                    }}
                >
                    <TablePlaceholder />
                </div>

                {/* Overlay */}
                <div
                    style={{
                        position: 'absolute',
                        inset: 0,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        background: 'rgba(255,255,255,0.15)',
                    }}
                >
                    <div
                        style={{
                            background: 'white',
                            borderRadius: 12,
                            padding: '2.5rem 2rem',
                            maxWidth: 440,
                            width: '100%',
                            textAlign: 'center',
                            boxShadow: '0 8px 48px rgba(0,0,0,0.18)',
                        }}
                    >
                        <LockIcon />
                        <h3 style={{ margin: '0 0 10px', fontSize: '1.1rem', fontWeight: 700 }}>
                            {modalTitle}
                        </h3>
                        <p
                            className="text-sm text-muted-foreground"
                            style={{ margin: '0 0 24px', lineHeight: 1.6 }}
                        >
                            {modalDesc}
                        </p>
                        <a
                            href={ctaUrl}
                            target={isInstalled ? '_self' : '_blank'}
                            rel="noreferrer"
                            className="sp-btn sp-btn--primary"
                            style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}
                        >
                            {ctaLabel} →
                        </a>
                    </div>
                </div>
            </div>
        </>
    )
}

export const SubscriptionsLockedPage = () => (
    <LockedFeaturePage
        title={__('Subscriptions', 'smartpay')}
        subtitle={__('Manage recurring payments and billing cycles', 'smartpay')}
        excerpt={__(
            'Create subscription plans, manage billing cycles, and let customers self-manage — all in one place.',
            'smartpay'
        )}
    />
)

export const ReportsLockedPage = () => (
    <LockedFeaturePage
        title={__('Reports', 'smartpay')}
        subtitle={__('Insights and analytics for your business', 'smartpay')}
        excerpt={__(
            'Track revenue, monitor growth, and analyze customer behavior with detailed, filterable reports.',
            'smartpay'
        )}
    />
)
