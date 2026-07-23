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

const ScreenshotPreview = ({ src }) => (
    <div
        style={{
            borderRadius: 8,
            border: '1px solid #e5e7eb',
            overflow: 'hidden',
            lineHeight: 0,
        }}
    >
        <img
            src={src}
            alt=""
            aria-hidden="true"
            style={{ width: '100%', display: 'block', userSelect: 'none', pointerEvents: 'none' }}
        />
    </div>
)

const LockedFeaturePage = ({ title, subtitle, excerpt, previewImage }) => {
    const proData = window.smartpayProData || {}
    const isInstalled = proData.isInstalled ?? false
    const licenseUrl =
        proData.licenseUrl ||
        (window.smartpay?.adminUrl ?? '') + '?page=smartpay-setting&tab=licenses'
    const upgradeUrl = 'https://wpsmartpay.com/pricing?utm_source=plugin&utm_medium=locked-page&utm_campaign=upgrade'

    const ctaUrl = isInstalled ? licenseUrl : upgradeUrl
    const ctaLabel = isInstalled
        ? __('Activate Your License', 'smartpay')
        : __('Upgrade to WPSmartPay Pro', 'smartpay')
    const modalTitle = isInstalled
        ? __('License activation required', 'smartpay')
        : __('Unlock', 'smartpay') + ' ' + title
    const modalDesc = isInstalled
        ? __(
              'Your WPSmartPay Pro license needs to be activated to access this feature.',
              'smartpay'
          )
        : excerpt

    const pluginUrl = window.smartpay?.pluginUrl ?? ''
    const imgSrc = previewImage ? `${pluginUrl}/img/${previewImage}` : null

    return (
        <>
            <Header title={title} subtitle={subtitle} />
            <div className="sp-content-wide" style={{ position: 'relative' }}>
                {/* Blurred screenshot preview */}
                <div
                    style={{
                        filter: 'blur(3px)',
                        pointerEvents: 'none',
                        userSelect: 'none',
                        opacity: 0.9,
                        transform: 'scale(1.02)',
                        transformOrigin: 'top center',
                    }}
                >
                    {imgSrc ? <ScreenshotPreview src={imgSrc} /> : null}
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
                            'linear-gradient(180deg, rgba(249,250,251,0.1) 0%, rgba(249,250,251,0.55) 100%)',
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
        previewImage="subscription-preview.jpg"
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
        previewImage="report-preview.jpg"
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
        previewImage="invoice-preview.jpg"
    />
)

export const WebhooksLockedPage = () => (
    <LockedFeaturePage
        title={__('Webhooks', 'smartpay')}
        subtitle={__('Send real-time event notifications to external services', 'smartpay')}
        excerpt={__(
            'Deliver payment, subscription, and invoice events instantly to n8n, Zapier, Make.com, or any HTTP endpoint — no polling needed.',
            'smartpay'
        )}
    />
)
