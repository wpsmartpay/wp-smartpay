import apiFetch from '@wordpress/api-fetch'
import { useEffect, useRef, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import {
    DollarSign,
    CreditCard,
    Activity,
    XCircle,
    Receipt,
    Package,
    UserCheck,
    FileText,
    Settings,
    Plug,
    BarChart3,
    HelpCircle,
    ChevronRight,
    Plus,
} from 'lucide-react'
import { Header } from '../components/header'
import { GettingStartedBanner } from '../components/GettingStartedBanner'

const { adminUrl, apiNonce, options } = window.smartpay

// ─── Helpers ──────────────────────────────────────────────────────────────────
const decodeHtmlEntity = (str) => {
    if (!str) return ''
    const txt = document.createElement('textarea')
    txt.innerHTML = str
    return txt.value
}
const currencySymbol = decodeHtmlEntity(options?.currencySymbol) || '$'

const formatRevenue = (amount) =>
    `${currencySymbol}${Number(amount || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`

const buildUrl = (period) => {
    const url = new URL(`${window.smartpay.restUrl}/v1/dashboard`)
    url.searchParams.set('period', period)
    return url.toString()
}

const timeAgo = (dateStr) => {
    if (!dateStr) return ''
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
    if (diff < 60)    return __('just now', 'smartpay')
    if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
    return `${Math.floor(diff / 86400)}d ago`
}

const avatarColor = (email) => {
    let h = 0
    for (let i = 0; i < (email || '').length; i++) h = (h + email.charCodeAt(i)) % 8
    return h
}

const avatarInitials = (email) => {
    const [local] = (email || '').split('@')
    return local.slice(0, 2).toUpperCase()
}

// ─── Period config ────────────────────────────────────────────────────────────
const PERIODS = [
    { key: 'today', label: __('Today', 'smartpay') },
    { key: 'week',  label: __('Week', 'smartpay') },
    { key: 'month', label: __('Month', 'smartpay') },
]

// ─── Management groups ────────────────────────────────────────────────────────
const MANAGEMENT_GROUPS = [
    {
        label: __('PAYMENTS & PRODUCTS', 'smartpay'),
        items: [
            { label: __('Payments', 'smartpay'),   icon: Receipt,   hash: '/payments' },
            { label: __('Products', 'smartpay'),   icon: Package,   hash: '/products' },
            { label: __('Customers', 'smartpay'),  icon: UserCheck, hash: '/customers' },
            { label: __('Forms', 'smartpay'),      icon: FileText,  hash: '/forms' },
        ],
    },
    {
        label: __('CONFIGURATION', 'smartpay'),
        items: [
            { label: __('Settings', 'smartpay'),      icon: Settings,   adminPage: 'smartpay-setting' },
            { label: __('Integrations', 'smartpay'),  icon: Plug,       adminPage: 'smartpay-integrations' },
            { label: __('Reports', 'smartpay'),       icon: BarChart3,  hash: '/reports' },
            { label: __('Support', 'smartpay'),       icon: HelpCircle, adminPage: 'smartpay-support' },
        ],
    },
]

// ─── Reusable detail card ─────────────────────────────────────────────────────
const DetailCard = ({ title, badge, action, children }) => (
    <div className="sp-detail-card">
        <div className="sp-detail-card__header">
            <span className="sp-detail-card__title">{title}</span>
            {badge && <span className="sp-detail-card__badge">{badge}</span>}
            {action && <span style={{ marginLeft: 'auto' }}>{action}</span>}
        </div>
        <div className="sp-detail-card__body">{children}</div>
    </div>
)

// ─── % change indicator ───────────────────────────────────────────────────────
const ChangeStat = ({ current, prev }) => {
    if (prev === 0 && current === 0) return null
    if (prev === 0) return <span className="sp-stat__change sp-stat__change--up">New</span>

    const pct = Math.round(((current - prev) / Math.abs(prev)) * 100)
    if (pct > 0) return <span className="sp-stat__change sp-stat__change--up">↑ +{pct}%</span>
    if (pct < 0) return <span className="sp-stat__change sp-stat__change--down">↓ {pct}%</span>
    return <span className="sp-stat__change sp-stat__change--flat">0%</span>
}

// ─── Single stat card ─────────────────────────────────────────────────────────
const StatCard = ({ icon: Icon, label, value, loading }) => (
    <div className="sp-stat">
        <p className="sp-stat__label">
            {Icon && <Icon style={{ width: 12, height: 12, display: 'inline', marginRight: 5, verticalAlign: 'middle', opacity: 0.7 }} />}
            {label}
        </p>
        <p className="sp-stat__value">{loading ? '—' : value}</p>
    </div>
)

// ─── Dashboard ────────────────────────────────────────────────────────────────
const now        = new Date()
const monthLabel = now.toLocaleString('default', { month: 'long', year: 'numeric' })

export const Dashboard = () => {
    const [period, setPeriod]            = useState('month')
    const [data, setData]                = useState(null)
    const [statsLoading, setStatsLoad]   = useState(true)
    const [recentPayments, setRecent]    = useState([])
    const [recentLoading, setRecentLoad] = useState(true)
    const recentSet                      = useRef(false)

    useEffect(() => {
        setStatsLoad(true)
        apiFetch({
            path:    buildUrl(period),
            headers: { 'X-WP-Nonce': apiNonce },
        })
            .then((res) => {
                setData(res)
                if (!recentSet.current) {
                    setRecent(res.recent_payments || [])
                    recentSet.current = true
                    setRecentLoad(false)
                }
            })
            .finally(() => setStatsLoad(false))
    }, [period])

    const curr = data?.period_stats          || {}
    const prev = data?.previous_period_stats || {}

    const getHref = (item) =>
        item.adminPage
            ? `${adminUrl}?page=${item.adminPage}`
            : `${adminUrl}?page=smartpay#${item.hash}`

    return (
        <>
            <Header
                title={__('Dashboard', 'smartpay')}
                subtitle={__('Overview of your payment activity', 'smartpay')}
            />

            <div className="sp-layout">

                {/* ── Page title + period filter ───────────────────────────── */}
                <div className="sp-page-title__inner" style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16 }}>
                    <div>
                        <h1 className="sp-page-title__heading">{__('Dashboard', 'smartpay')}</h1>
                        <p className="sp-page-title__sub">
                            {__('Overview of your payment activity', 'smartpay')} — {monthLabel}
                        </p>
                    </div>
                    <div className="sp-filter-tabs" style={{ marginBottom: 0, flexShrink: 0 }}>
                        {PERIODS.map(({ key, label }) => (
                            <button
                                key={key}
                                type="button"
                                onClick={() => setPeriod(key)}
                                className={`sp-filter-tab${period === key ? ' sp-filter-tab--active' : ''}`}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* ── Getting Started (dismissible) ─────────────────────────── */}
                <GettingStartedBanner /> 

                {/* ── 4 stat cards ─────────────────────────────────────────── */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 20 }}>
                    <StatCard icon={DollarSign} label={__('Total Revenue', 'smartpay')}       value={formatRevenue(curr.revenue)}    loading={statsLoading} />
                    <StatCard icon={CreditCard} label={__('Completed Payments', 'smartpay')}  value={curr.completed_count ?? 0}      loading={statsLoading} />
                    <StatCard icon={Activity}   label={__('Pending', 'smartpay')}             value={curr.pending_count ?? 0}        loading={statsLoading} />
                    <StatCard icon={XCircle}    label={__('Failed', 'smartpay')}              value={curr.failed_count ?? 0}         loading={statsLoading} />
                </div>

                {/* ── Two equal columns: Payments | Right sidebar ───────────── */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20, alignItems: 'start' }}>

                    {/* Left — Recent Payments (auto-expands with list) */}
                    <DetailCard
                        title={__('RECENT PAYMENTS', 'smartpay')}
                        action={
                            <a
                                href={`${adminUrl}?page=smartpay#/payments`}
                                style={{ fontSize: 12, color: 'var(--sp-text-muted)', textDecoration: 'none', fontWeight: 500 }}
                            >
                                {__('Open payments →', 'smartpay')}
                            </a>
                        }
                    >
                        {recentLoading ? (
                            <p style={{ color: 'var(--sp-text-muted)', margin: 0, fontSize: 13 }}>{__('Loading…', 'smartpay')}</p>
                        ) : recentPayments.length === 0 ? (
                            <div className="sp-empty" style={{ padding: '24px 0' }}>
                                <div className="sp-empty__icon">💳</div>
                                <div className="sp-empty__title">{__('No payments yet', 'smartpay')}</div>
                                <div className="sp-empty__desc">{__('Payments will appear here once received.', 'smartpay')}</div>
                            </div>
                        ) : (
                            <>
                                <table className="sp-kv-table" style={{ marginBottom: 14 }}>
                                    <tbody>
                                        {recentPayments.slice(0, 10).map((payment) => (
                                            <tr key={payment.id}>
                                                <td style={{ width: 'auto', paddingRight: 12 }}>
                                                    <div className="sp-customer">
                                                        <div className="sp-avatar sp-avatar--sm" data-color={avatarColor(payment.email)}>
                                                            {avatarInitials(payment.email)}
                                                        </div>
                                                        <div className="sp-customer__info">
                                                            <a href={payment.view_url} className="sp-customer__name" style={{ textDecoration: 'none', color: 'inherit' }}>
                                                                {payment.email}
                                                            </a>
                                                            {payment.source_type && (
                                                                <div className="sp-customer__email">
                                                                    {payment.source_name ? `${payment.source_type}: ${payment.source_name}` : payment.source_type}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style={{ whiteSpace: 'nowrap' }}>
                                                    <span className="sp-detail-card__badge">#{payment.id}</span>
                                                </td>
                                                <td style={{ textAlign: 'right', color: 'var(--sp-text-muted)', fontSize: 12, whiteSpace: 'nowrap' }}>
                                                    {timeAgo(payment.completed_at)}
                                                </td>
                                                <td style={{ textAlign: 'right', fontWeight: 600, whiteSpace: 'nowrap', fontVariantNumeric: 'tabular-nums' }}>
                                                    <a href={payment.view_url} style={{ textDecoration: 'none', color: 'inherit' }}>
                                                        {formatRevenue(payment.amount)}
                                                    </a>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                <a
                                    href={`${adminUrl}?page=smartpay#/payments`}
                                    className="sp-btn sp-btn--outline"
                                    style={{ textDecoration: 'none', fontSize: 12, height: 30, padding: '0 12px' }}
                                >
                                    {__('View all payments →', 'smartpay')}
                                </a>
                            </>
                        )}
                    </DetailCard>

                    {/* Right — CTAs at top, then Navigation card */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>

                        {/* CTA: Add New Product */}
                        <div className="sp-detail-card">
                            <div className="sp-detail-card__body" style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                                <div style={{ width: 40, height: 40, borderRadius: 8, background: 'var(--sp-brand-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                    <Package style={{ width: 18, height: 18, color: 'var(--sp-brand)' }} />
                                </div>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--sp-text)', marginBottom: 1 }}>
                                        {__('Add New Product', 'smartpay')}
                                    </div>
                                    <div style={{ fontSize: 11.5, color: 'var(--sp-text-muted)', lineHeight: 1.4 }}>
                                        {__('Create a product for customers to purchase.', 'smartpay')}
                                    </div>
                                </div>
                                <a
                                    href={`${adminUrl}?page=smartpay#/products/create`}
                                    className="sp-btn sp-btn--primary"
                                    style={{ textDecoration: 'none', flexShrink: 0, gap: 5, fontSize: 12, height: 30, padding: '0 12px' }}
                                >
                                    <Plus style={{ width: 12, height: 12 }} />
                                    {__('Add Product', 'smartpay')}
                                </a>
                            </div>
                        </div>

                        {/* CTA: Create a Payment Form */}
                        <div className="sp-detail-card">
                            <div className="sp-detail-card__body" style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                                <div style={{ width: 40, height: 40, borderRadius: 8, background: 'var(--sp-brand-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                    <FileText style={{ width: 18, height: 18, color: 'var(--sp-brand)' }} />
                                </div>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--sp-text)', marginBottom: 1 }}>
                                        {__('Create a Payment Form', 'smartpay')}
                                    </div>
                                    <div style={{ fontSize: 11.5, color: 'var(--sp-text-muted)', lineHeight: 1.4 }}>
                                        {__('Build a form to collect payments from your site.', 'smartpay')}
                                    </div>
                                </div>
                                <a
                                    href={`${adminUrl}?page=smartpay#/native-forms`}
                                    className="sp-btn sp-btn--primary"
                                    style={{ textDecoration: 'none', flexShrink: 0, gap: 5, fontSize: 12, height: 30, padding: '0 12px' }}
                                >
                                    <Plus style={{ width: 12, height: 12 }} />
                                    {__('Create Form', 'smartpay')}
                                </a>
                            </div>
                        </div>

                        {/* Navigation — Management + Configuration merged */}
                        <div className="sp-detail-card">
                            <div className="sp-detail-card__header">
                                <span className="sp-detail-card__title">{__('NAVIGATION', 'smartpay')}</span>
                            </div>
                            <div className="sp-detail-card__body" style={{ padding: 0 }}>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr' }}>
                                    <div style={{ borderRight: '1px solid var(--sp-border)' }}>
                                        <div style={{ padding: '10px 14px 4px', fontSize: 10, fontWeight: 700, color: 'var(--sp-text-subtle)', textTransform: 'uppercase', letterSpacing: '0.07em' }}>
                                            {__('Management', 'smartpay')}
                                        </div>
                                        {MANAGEMENT_GROUPS[0].items.map((item) => (
                                            <a key={item.label} href={getHref(item)}
                                                style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 14px', textDecoration: 'none', color: 'var(--sp-text)', fontWeight: 500, fontSize: 13 }}
                                                onMouseOver={(e) => e.currentTarget.style.background = 'var(--sp-surface-muted)'}
                                                onMouseOut={(e)  => e.currentTarget.style.background = 'transparent'}
                                            >
                                                <div style={{ width: 26, height: 26, borderRadius: 6, background: 'var(--sp-brand-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                    <item.icon style={{ width: 12, height: 12, color: 'var(--sp-brand)' }} />
                                                </div>
                                                {item.label}
                                            </a>
                                        ))}
                                        <div style={{ height: 10 }} />
                                    </div>
                                    <div>
                                        <div style={{ padding: '10px 14px 4px', fontSize: 10, fontWeight: 700, color: 'var(--sp-text-subtle)', textTransform: 'uppercase', letterSpacing: '0.07em' }}>
                                            {__('Configuration', 'smartpay')}
                                        </div>
                                        {MANAGEMENT_GROUPS[1].items.map((item) => (
                                            <a key={item.label} href={getHref(item)}
                                                style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 14px', textDecoration: 'none', color: 'var(--sp-text)', fontWeight: 500, fontSize: 13 }}
                                                onMouseOver={(e) => e.currentTarget.style.background = 'var(--sp-surface-muted)'}
                                                onMouseOut={(e)  => e.currentTarget.style.background = 'transparent'}
                                            >
                                                <div style={{ width: 26, height: 26, borderRadius: 6, background: 'var(--sp-brand-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                    <item.icon style={{ width: 12, height: 12, color: 'var(--sp-brand)' }} />
                                                </div>
                                                {item.label}
                                            </a>
                                        ))}
                                        <div style={{ height: 10 }} />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>


            </div>

        </>
    )
}
