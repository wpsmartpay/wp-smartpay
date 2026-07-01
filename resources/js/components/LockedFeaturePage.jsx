import { __ } from '@wordpress/i18n'
import { Header } from './header'

const LockIcon = () => (
    <div
        style={{
            width: 52,
            height: 52,
            borderRadius: 14,
            background: 'var(--sp-surface-muted)',
            border: '1px solid var(--sp-border)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            flexShrink: 0,
        }}
    >
        <svg
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="var(--sp-text)"
            strokeWidth="1.75"
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
                <div style={{ height: 12, background: '#eceef1', borderRadius: 2, width: '55%' }} />
                <div
                    style={{
                        height: 20,
                        background: '#eceef1',
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
    const proData = window.smartpayProData || {}
    const isInstalled = proData.isInstalled ?? false
    // Prefer the server-built URL (avoids the site domain being added twice).
    const licenseUrl =
        proData.licenseUrl ||
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
                        padding: 24,
                        background:
                            'linear-gradient(180deg, rgba(249,250,251,0.4) 0%, rgba(249,250,251,0.8) 100%)',
                    }}
                >
                    <div
                        style={{
                            background: '#fff',
                            borderRadius: 16,
                            padding: '30px 30px 26px',
                            maxWidth: 400,
                            width: '100%',
                            border: '1px solid var(--sp-border)',
                            boxShadow: 'var(--sp-shadow-md)',
                        }}
                    >
                        <LockIcon />
                        <h3
                            style={{
                                margin: '20px 0 8px',
                                fontSize: '1.2rem',
                                fontWeight: 700,
                                letterSpacing: '-0.01em',
                                color: 'var(--sp-text)',
                            }}
                        >
                            {modalTitle}
                        </h3>
                        <p
                            style={{
                                margin: '0 0 22px',
                                fontSize: 13.5,
                                lineHeight: 1.65,
                                color: 'var(--sp-text-muted)',
                            }}
                        >
                            {modalDesc}
                        </p>
                        <a
                            href={ctaUrl}
                            target={isInstalled ? '_self' : '_blank'}
                            rel="noreferrer"
                            className="sp-btn sp-btn--primary"
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: 6,
                                height: 42,
                                textDecoration: 'none',
                            }}
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

export const InvoicesLockedPage = () => (
    <LockedFeaturePage
        title={__('Invoices', 'smartpay')}
        subtitle={__('Send payment requests to customers', 'smartpay')}
        excerpt={__(
            'Create and send professional invoice payment links to customers directly from your dashboard.',
            'smartpay'
        )}
    />
)
