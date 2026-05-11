import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
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
} from 'lucide-react'
import { Header } from '../components/header'

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

const avatarInitials = (email) => {
    const [local] = (email || '').split('@')
    return local.slice(0, 2).toUpperCase()
}

// ─── Period config ────────────────────────────────────────────────────────────
const PERIODS = [
    { key: 'today', label: __('Today', 'smartpay') },
    { key: 'week',  label: __('Week to date', 'smartpay') },
    { key: 'month', label: __('Month to date', 'smartpay') },
]

// ─── Management groups ────────────────────────────────────────────────────────
const MANAGEMENT_GROUPS = [
    {
        label: __('PAYMENTS & PRODUCTS', 'smartpay'),
        items: [
            { label: __('View Payments', 'smartpay'),  icon: Receipt,   hash: '/payments' },
            { label: __('Products', 'smartpay'),       icon: Package,   hash: '/products' },
            { label: __('Customers', 'smartpay'),      icon: UserCheck, hash: '/customers' },
            { label: __('Forms', 'smartpay'),          icon: FileText,  hash: '/forms' },
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

// ─── % Change badge ───────────────────────────────────────────────────────────
function ChangeBadge({ current, prev }) {
    let label, cls

    if (prev === 0 && current === 0) {
        label = '0%'
        cls   = 'text-muted-foreground bg-muted'
    } else if (prev === 0) {
        label = __('New', 'smartpay')
        cls   = 'text-green-700 bg-green-50'
    } else {
        const pct = Math.round(((current - prev) / Math.abs(prev)) * 100)
        if (pct > 0) {
            label = `+${pct}%`
            cls   = 'text-green-700 bg-green-50'
        } else if (pct < 0) {
            label = `${pct}%`
            cls   = 'text-red-600 bg-red-50'
        } else {
            label = '0%'
            cls   = 'text-muted-foreground bg-muted'
        }
    }

    return (
        <span className={`text-xs font-semibold px-2 py-0.5 rounded tabular-nums ${cls}`}>
            {label}
        </span>
    )
}

// ─── Single stat cell (used in 2×2 grid) ─────────────────────────────────────
function StatCell({ icon: Icon, title, value, current, prev, loading, borderRight, borderBottom }) {
    const borders = [
        borderRight  ? 'border-r border-border' : '',
        borderBottom ? 'border-b border-border' : '',
    ].filter(Boolean).join(' ')

    return (
        <div className={`p-6 ${borders}`}>
            <div className="flex items-center gap-2 text-sm text-muted-foreground mb-3">
                {Icon && <Icon className="w-4 h-4" />}
                {title}
            </div>
            <div className="flex items-end justify-between gap-2">
                <span className="text-3xl font-bold tracking-tight text-card-foreground">
                    {loading ? '—' : value}
                </span>
                {!loading && (
                    <ChangeBadge current={current ?? 0} prev={prev ?? 0} />
                )}
            </div>
        </div>
    )
}

// ─── Dashboard ────────────────────────────────────────────────────────────────
export const Dashboard = () => {
    const [period, setPeriod]   = useState('month')
    const [data, setData]        = useState(null)
    const [loading, setLoading]  = useState(true)

    const { Card, CardHeader, CardTitle, CardContent, CardFooter } = window.WPSmartPayUI

    useEffect(() => {
        setLoading(true)
        apiFetch({
            path:    buildUrl(period),
            headers: { 'X-WP-Nonce': apiNonce },
        })
            .then(setData)
            .finally(() => setLoading(false))
    }, [period])

    const curr           = data?.period_stats          || {}
    const prev           = data?.previous_period_stats || {}
    const recentPayments = data?.recent_payments        || []

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

            <div className="sp-page-content sp-page-content--sm">

                {/* ── Stats Overview ──────────────────────────────────────── */}
                <Card>
                    <CardHeader className="border-b border-border pb-0">
                        <CardTitle>{__('Stats Overview', 'smartpay')}</CardTitle>
                    </CardHeader>

                    {/* Period tabs */}
                    <div className="sp-tabs__nav">
                        <nav className="flex gap-0">
                            {PERIODS.map(({ key, label }) => (
                                <button
                                    key={key}
                                    type="button"
                                    onClick={() => setPeriod(key)}
                                    className={period === key
                                        ? 'sp-tabs__item sp-tabs__item--active'
                                        : 'sp-tabs__item'
                                    }
                                >
                                    {label}
                                </button>
                            ))}
                        </nav>
                    </div>

                    {/* 2×2 stats grid */}
                    <div className="grid grid-cols-2">
                        <StatCell
                            icon={DollarSign}
                            title={__('Total Revenue', 'smartpay')}
                            value={formatRevenue(curr.revenue)}
                            current={curr.revenue}
                            prev={prev.revenue}
                            loading={loading}
                            borderRight
                            borderBottom
                        />
                        <StatCell
                            icon={CreditCard}
                            title={__('Completed Payments', 'smartpay')}
                            value={curr.completed_count ?? 0}
                            current={curr.completed_count}
                            prev={prev.completed_count}
                            loading={loading}
                            borderBottom
                        />
                        <StatCell
                            icon={Activity}
                            title={__('Pending', 'smartpay')}
                            value={curr.pending_count ?? 0}
                            current={curr.pending_count}
                            prev={prev.pending_count}
                            loading={loading}
                            borderRight
                        />
                        <StatCell
                            icon={XCircle}
                            title={__('Failed', 'smartpay')}
                            value={curr.failed_count ?? 0}
                            current={curr.failed_count}
                            prev={prev.failed_count}
                            loading={loading}
                        />
                    </div>

                    <CardFooter className="border-t border-border pt-4 pb-4">
                        <a
                            href={`${adminUrl}?page=smartpay#/reports`}
                            className="text-sm text-primary hover:underline no-underline font-medium"
                        >
                            {__('View detailed reports →', 'smartpay')}
                        </a>
                    </CardFooter>
                </Card>

                {/* ── Recent Payments ─────────────────────────────────────── */}
                <Card>
                    <CardHeader>
                        <CardTitle>{__('Recent Payments', 'smartpay')}</CardTitle>
                    </CardHeader>
                    <CardContent className="pt-0">
                        {loading ? (
                            <div className="sp-state-loading">{__('Loading…', 'smartpay')}</div>
                        ) : recentPayments.length === 0 ? (
                            <div className="sp-state-empty">{__('No payments yet.', 'smartpay')}</div>
                        ) : (
                            <div className="flex flex-col divide-y divide-border">
                                {recentPayments.map((payment) => (
                                    <a
                                        key={payment.id}
                                        href={payment.view_url}
                                        className="flex items-center gap-4 py-3 no-underline -mx-6 px-6 hover:bg-muted/30 transition-colors"
                                    >
                                        <div className="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-muted text-xs font-semibold text-muted-foreground select-none">
                                            {avatarInitials(payment.email)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2">
                                                <p className="text-sm font-medium text-card-foreground truncate leading-none">
                                                    {payment.email}
                                                </p>
                                                <span className="text-xs text-muted-foreground flex-shrink-0 tabular-nums">
                                                    #{payment.id}
                                                </span>
                                            </div>
                                            {payment.source_type && (
                                                <p className="text-xs text-muted-foreground truncate mt-1">
                                                    {payment.source_name
                                                        ? `${payment.source_type}: ${payment.source_name}`
                                                        : payment.source_type
                                                    }
                                                </p>
                                            )}
                                        </div>
                                        <span className="text-xs text-muted-foreground flex-shrink-0">
                                            {timeAgo(payment.completed_at)}
                                        </span>
                                        <span className="text-sm font-semibold text-card-foreground tabular-nums flex-shrink-0">
                                            {formatRevenue(payment.amount)}
                                        </span>
                                    </a>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* ── SmartPay Management ──────────────────────────────────── */}
                <Card>
                    <CardHeader>
                        <CardTitle>{__('SmartPay Management', 'smartpay')}</CardTitle>
                    </CardHeader>
                    <CardContent className="pt-0 flex flex-col gap-6">
                        {MANAGEMENT_GROUPS.map((group) => (
                            <div key={group.label}>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">
                                    {group.label}
                                </p>
                                <div className="sp-grid sp-grid--2">
                                    {group.items.map((item) => (
                                        <a
                                            key={item.label}
                                            href={getHref(item)}
                                            className="flex items-center gap-3 py-2 text-sm font-semibold text-card-foreground no-underline hover:text-primary transition-colors"
                                        >
                                            <item.icon className="w-4 h-4 text-primary flex-shrink-0" />
                                            {item.label}
                                        </a>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>


            </div>
        </>
    )
}
