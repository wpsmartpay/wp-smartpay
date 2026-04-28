import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import {
    DollarSign,
    CreditCard,
    Activity,
    XCircle,
    Package,
    FileText,
    CalendarRange,
    ExternalLink,
    Receipt,
    UserCheck,
    ArrowUpRight,
} from 'lucide-react'
import { Report } from '../components/report/report'
import { StatCard } from '../components/stat-card'
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

const emailToInitials = (email = '') => {
    const handle = email.split('@')[0] || ''
    const parts  = handle.split(/[._-]/).filter(Boolean)
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
    return handle.slice(0, 2).toUpperCase()
}

const emailToName = (email = '') => {
    const handle = email.split('@')[0] || email
    return handle
        .split(/[._-]/)
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ')
}

// ─── Period config ────────────────────────────────────────────────────────────
const PERIODS = [
    { key: 'today', label: __('Today', 'smartpay') },
    { key: 'week',  label: __('This Week', 'smartpay') },
    { key: 'month', label: __('This Month', 'smartpay') },
]

const getPeriodLabel = (period) => {
    const now = new Date()
    const fmt = (d) =>
        d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' })

    if (period === 'today') return fmt(now)

    if (period === 'week') {
        const mon = new Date(now)
        mon.setDate(now.getDate() - ((now.getDay() + 6) % 7))
        return `${fmt(mon)} → ${fmt(now)}`
    }

    // month
    const start = new Date(now.getFullYear(), now.getMonth(), 1)
    return `${fmt(start)} → ${fmt(now)}`
}

// ─── URL builder ──────────────────────────────────────────────────────────────
const buildDashboardUrl = (period) => {
    const url = new URL(`${window.smartpay.restUrl}/v1/dashboard`)
    url.searchParams.set('period', period)
    return url.toString()
}

// ─── Quick links ──────────────────────────────────────────────────────────────
const QUICK_LINKS = [
    { label: __('Products', 'smartpay'),  icon: Package,   hash: '/products' },
    { label: __('Forms', 'smartpay'),     icon: FileText,  hash: '/forms' },
    { label: __('Payments', 'smartpay'),  icon: Receipt,   hash: '/payments' },
    { label: __('Customers', 'smartpay'), icon: UserCheck, hash: '/customers' },
]

// ─── Dashboard ────────────────────────────────────────────────────────────────
export const Dashboard = () => {
    const [period, setPeriod]   = useState('month')
    const [data, setData]       = useState(null)
    const [loading, setLoading] = useState(true)

    const {
        Card,
        CardHeader,
        CardTitle,
        CardDescription,
        CardContent,
    } = window.WPSmartPayUI

    useEffect(() => {
        setLoading(true)
        apiFetch({
            path:    buildDashboardUrl(period),
            headers: { 'X-WP-Nonce': apiNonce },
        })
            .then(setData)
            .finally(() => setLoading(false))
    }, [period])

    const periodStats    = data?.period_stats    || {}
    const monthlyChart   = data?.monthly_chart   || []
    const recentPayments = data?.recent_payments || []

    // ─── Stat cards ───────────────────────────────────────────────────────────
    const STAT_CARDS = [
        {
            title:  __('Total Revenue', 'smartpay'),
            value:  loading ? '—' : formatRevenue(periodStats.revenue),
            change: __('Period total', 'smartpay'),
            icon:   DollarSign,
        },
        {
            title:  __('Completed', 'smartpay'),
            value:  loading ? '—' : `+${periodStats.completed_count || 0}`,
            change: __('Payments completed', 'smartpay'),
            icon:   CreditCard,
        },
        {
            title:  __('Pending', 'smartpay'),
            value:  loading ? '—' : `${periodStats.pending_count || 0}`,
            change: __('Awaiting completion', 'smartpay'),
            icon:   Activity,
        },
        {
            title:  __('Failed', 'smartpay'),
            value:  loading ? '—' : `${periodStats.failed_count || 0}`,
            change: __('Failed payments', 'smartpay'),
            icon:   XCircle,
        },
    ]

    // ─── Area chart options (shadcn-style) ────────────────────────────────────
    const chartSeries = [
        {
            name: __('Products', 'smartpay'),
            data: monthlyChart.map((d) => Number(d.product_purchase || 0)),
        },
        {
            name: __('Forms', 'smartpay'),
            data: monthlyChart.map((d) => Number(d.form_payment || 0)),
        },
    ]

    const chartOptions = {
        chart: {
            type:       'area',
            toolbar:    { show: false },
            zoom:       { enabled: false },
            fontFamily: 'inherit',
            animations: { enabled: true, speed: 500 },
        },
        stroke:     { curve: 'smooth', width: 2 },
        fill: {
            type:     'gradient',
            gradient: { opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 95, 100] },
        },
        colors:     ['#3858e9', '#22c55e'],
        dataLabels: { enabled: false },
        xaxis: {
            type:       'category',
            categories: monthlyChart.map((d) => d.date),
            labels: {
                style:     { fontSize: '11px', colors: '#6b7280' },
                rotate:    0,
                formatter: (val, idx) => (idx % 5 === 0 ? val : ''),
            },
            axisBorder: { show: false },
            axisTicks:  { show: false },
        },
        yaxis: {
            labels: {
                formatter: (v) => `${currencySymbol}${Number(v).toLocaleString()}`,
                style:     { fontSize: '11px', colors: '#6b7280' },
            },
        },
        grid: {
            borderColor:     '#f3f4f6',
            strokeDashArray: 4,
            xaxis:           { lines: { show: false } },
        },
        tooltip: {
            y: { formatter: (v) => formatRevenue(v) },
        },
        legend: {
            show:            true,
            position:        'top',
            horizontalAlign: 'right',
            fontSize:        '12px',
            labels:          { colors: '#6b7280' },
        },
    }

    return (
        <>
            {/* ── Page header ──────────────────────────────────────────────── */}
            <Header
                title={__('Dashboard', 'smartpay')}
                subtitle={__('Overview of your payment activity', 'smartpay')}
            />

            {/* ── Period selector + date range badge ───────────────────────── */}
            <div className="flex items-center justify-between px-4 pt-4 pb-0 max-w-7xl mx-auto flex-wrap gap-3">
                <div className="flex items-center gap-2 text-sm text-muted-foreground border border-border rounded-lg px-3 py-1.5 bg-background">
                    <CalendarRange className="w-4 h-4" />
                    <span>{getPeriodLabel(period)}</span>
                </div>

                <div className="flex items-center gap-0.5 rounded-lg border border-border bg-muted/40 p-1">
                    {PERIODS.map(({ key, label }) => (
                        <button
                            key={key}
                            type="button"
                            onClick={() => setPeriod(key)}
                            className={
                                period === key
                                    ? 'inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium bg-background shadow-sm text-foreground transition-all border-0 cursor-pointer'
                                    : 'inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-all bg-transparent border-0 cursor-pointer'
                            }
                        >
                            {label}
                        </button>
                    ))}
                </div>
            </div>

            <div className="p-4 max-w-7xl mx-auto flex flex-col gap-5">

                {/* ── Stat cards ─────────────────────────────────────────── */}
                <div className="grid grid-cols-4 gap-4 lg:grid-cols-4">
                    {STAT_CARDS.map((card) => (
                        <StatCard
                            key={card.title}
                            title={card.title}
                            value={card.value}
                            change={card.change}
                            icon={card.icon}
                        />
                    ))}
                </div>

                {/* ── Area chart (2/3) + Highlights + Quick Links (1/3) ──── */}
                <div className="grid grid-cols-3 gap-4 lg:grid-cols-3">

                    {/* Area chart */}
                    <div className="lg:col-span-2">
                        <Card className="h-full">
                            <CardHeader> 
                                <CardTitle>{__('Revenue Overview', 'smartpay')}</CardTitle>
                                <CardDescription>
                                    {__('Product purchases & form payments', 'smartpay')}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {loading ? (
                                    <div className="h-[300px] flex items-center justify-center text-muted-foreground text-sm">
                                        {__('Loading…', 'smartpay')}
                                    </div>
                                ) : (
                                    <Report
                                        type="area"
                                        height="300"
                                        series={chartSeries}
                                        options={chartOptions}
                                    />
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="flex flex-col gap-4">

                        {/* Quick Links */}
                        <Card>
                            <CardHeader>
                                <CardTitle>{__('Quick Links', 'smartpay')}</CardTitle>
                            </CardHeader>
                            <CardContent className="px-3 pb-3 pt-0">
                                <nav className="flex flex-col gap-0.5">
                                    {QUICK_LINKS.map(({ label, icon: Icon, hash }) => (
                                        <a
                                            key={label}
                                            href={`${adminUrl}?page=smartpay#${hash}`}
                                            className="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-muted/50 hover:text-foreground transition-colors no-underline group"
                                        >
                                            <Icon className="h-4 w-4 flex-shrink-0" />
                                            <span className="flex-1">{label}</span>
                                            <ExternalLink className="h-3 w-3 opacity-0 group-hover:opacity-40 transition-opacity" />
                                        </a>
                                    ))}
                                </nav>
                            </CardContent>
                        </Card>

                    </div>
                </div>

                {/* ── Recent Sales ────────────────────────────────────────── */}
                <Card>
                    <CardHeader>
                        <CardTitle>{__('Recent Sales', 'smartpay')}</CardTitle>
                        <CardDescription>
                            {loading
                                ? __('Loading…', 'smartpay')
                                : periodStats.completed_count
                                    ? `${periodStats.completed_count} ${__('completed payments this period.', 'smartpay')}`
                                    : __('No completed payments this period.', 'smartpay')
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="pt-0">
                        {loading ? (
                            <div className="p-4 text-center text-muted-foreground text-sm">
                                {__('Loading…', 'smartpay')}
                            </div>
                        ) : !recentPayments.length ? (
                            <div className="p-4 text-center text-muted-foreground text-sm">
                                {__('No recent payments.', 'smartpay')}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-border">
                                            <th className="pb-2 text-left text-xs font-medium text-muted-foreground pr-4 whitespace-nowrap">
                                                {__('Customer', 'smartpay')}
                                            </th>
                                            <th className="pb-2 text-left text-xs font-medium text-muted-foreground pr-4 whitespace-nowrap">
                                                {__('Product / Form', 'smartpay')}
                                            </th>
                                            <th className="pb-2 text-left text-xs font-medium text-muted-foreground pr-4 whitespace-nowrap">
                                                {__('Date', 'smartpay')}
                                            </th>
                                            <th className="pb-2 text-right text-xs font-medium text-muted-foreground whitespace-nowrap">
                                                {__('Amount', 'smartpay')}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recentPayments.map((payment) => {
                                            const initials    = emailToInitials(payment.email)
                                            const name        = emailToName(payment.email)
                                            const completedAt = payment.completed_at
                                                ? new Date(payment.completed_at).toLocaleString(undefined, {
                                                      day:    'numeric',
                                                      month:  'short',
                                                      year:   'numeric',
                                                      hour:   '2-digit',
                                                      minute: '2-digit',
                                                  })
                                                : '—'

                                            return (
                                                <tr
                                                    key={payment.id}
                                                    className="border-b border-border/50 last:border-0 hover:bg-muted/30 transition-colors group"
                                                >
                                                    {/* Customer */}
                                                    <td className="py-2 pr-4">
                                                        <div className="flex items-center gap-2.5 min-w-0">
                                                            <div className="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-muted text-xs font-semibold text-muted-foreground select-none">
                                                                {initials}
                                                            </div>
                                                            <div className="min-w-0">
                                                                <p className="font-medium text-card-foreground leading-none truncate max-w-[140px] m-0">
                                                                    {name}
                                                                </p>
                                                                <p className="text-xs text-muted-foreground truncate max-w-[140px] mt-0">
                                                                    {payment.email}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    {/* Product / Form name */}
                                                    <td className="py-3 pr-4">
                                                        <span className="text-card-foreground truncate max-w-[160px] block">
                                                            {payment.source_name || (
                                                                <span className="text-muted-foreground italic">—</span>
                                                            )}
                                                        </span>
                                                    </td>

                                                    {/* Date/time */}
                                                    <td className="py-3 pr-4 whitespace-nowrap text-muted-foreground text-xs">
                                                        {completedAt}
                                                    </td>

                                                    {/* Amount + quick-view */}
                                                    <td className="py-3 text-right whitespace-nowrap">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <span className="font-semibold text-card-foreground tabular-nums">
                                                                +{formatRevenue(payment.amount)}
                                                            </span>
                                                            {payment.view_url && (
                                                                <a
                                                                    href={payment.view_url}
                                                                    title={__('View payment', 'smartpay')}
                                                                    aria-label={__('View payment details', 'smartpay')}
                                                                    className="opacity-0 group-hover:opacity-100 transition-opacity text-muted-foreground hover:text-foreground no-underline flex-shrink-0"
                                                                >
                                                                    <ArrowUpRight className="h-3.5 w-3.5" />
                                                                </a>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            )
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>

            </div>
        </>
    )
}
