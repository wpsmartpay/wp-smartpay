import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import {
    LayoutDashboard,
    BarChart2,
    Activity,
    DollarSign,
    CheckCircle2,
    Clock,
    XCircle,
    Package,
    FileText,
    Users,
    ShoppingCart,
    Tag,
    Settings,
    ExternalLink,
    CalendarRange,
    ArrowUpRight,
    ArrowDownRight,
} from 'lucide-react'
import { Header } from '../components/header'
import { Report } from '../components/report/report'
import { StatCard } from '../components/stat-card'

import dayjs from 'dayjs'
const relativeTime = require('dayjs/plugin/relativeTime')
let utc = require('dayjs/plugin/utc')
dayjs.extend(relativeTime)
dayjs.extend(utc)

const { adminUrl, apiNonce, options } = window.smartpay

// Decode HTML entities that PHP may encode in wp_localize_script (e.g. &#36; → $)
const decodeHtmlEntity = (str) => {
    if (!str) return ''
    const txt = document.createElement('textarea')
    txt.innerHTML = str
    return txt.value
}
const currencySymbol = decodeHtmlEntity(options?.currencySymbol) || '$'

// ─── Period config ────────────────────────────────────────────────────────────
const PERIODS = [
    { key: 'today', label: __('Today', 'smartpay') },
    { key: 'week',  label: __('Week to date', 'smartpay') },
    { key: 'month', label: __('Month to date', 'smartpay') },
]

const PERIOD_DATE_LABELS = {
    today: () => dayjs().format('D MMM YYYY'),
    week:  () => `${dayjs().startOf('week').add(1,'day').format('D MMM')} – ${dayjs().format('D MMM YYYY')}`,
    month: () => `${dayjs().startOf('month').format('D MMM')} – ${dayjs().format('D MMM YYYY')}`,
}

// ─── Top-level tab navigation ─────────────────────────────────────────────────
const NAV_TABS = [
    { key: 'overview',    label: __('Overview', 'smartpay'),    icon: LayoutDashboard },
    { key: 'reports',     label: __('Reports', 'smartpay'),     icon: BarChart2 },
    { key: 'activities',  label: __('Activities', 'smartpay'),  icon: Activity },
]

// ─── Quick links ──────────────────────────────────────────────────────────────
const QUICK_LINKS = [
    { label: __('Products', 'smartpay'),  icon: Package,      url: `${adminUrl}?page=smartpay#/products` },
    { label: __('Forms', 'smartpay'),     icon: FileText,     url: `${adminUrl}?page=smartpay-form` },
    { label: __('Members', 'smartpay'),   icon: Users,        url: `${adminUrl}?page=smartpay#/members` },
    { label: __('Payments', 'smartpay'),  icon: ShoppingCart, url: `${adminUrl}?page=smartpay#/payments` },
    { label: __('Coupons', 'smartpay'),   icon: Tag,          url: `${adminUrl}?page=smartpay#/coupons` },
    { label: __('Settings', 'smartpay'),  icon: Settings,     url: `${adminUrl}?page=smartpay-setting` },
]

// ─── apiFetch URL builder ─────────────────────────────────────────────────────
const buildDashboardUrl = (period) => {
    const url = new URL(`${window.smartpay.restUrl}/v1/dashboard`)
    url.searchParams.set('period', period)
    return url.toString()
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const formatRevenue = (amount) =>
    `${currencySymbol}${Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

// ─── Dashboard ────────────────────────────────────────────────────────────────
export const Dashboard = () => {
    const [activeTab, setActiveTab]  = useState('overview')
    const [period, setPeriod]         = useState('month')
    const [data, setData]             = useState(null)
    const [loading, setLoading]       = useState(true)

    const {
        Header: UIHeader,
        Card,
        CardHeader,
        CardTitle,
        CardDescription,
        CardContent,
    } = window.WPSmartPayUI

    useEffect(() => {
        setLoading(true)
        apiFetch({
            path: buildDashboardUrl(period),
            headers: { 'X-WP-Nonce': apiNonce },
        })
            .then((response) => setData(response))
            .finally(() => setLoading(false))
    }, [period])

    const periodStats  = data?.period_stats  || {}
    const totals       = data?.totals        || {}
    const topProducts  = data?.top_products  || []
    const topForms     = data?.top_forms     || []
    const monthlyChart = data?.monthly_chart || []

    // ─── Stat cards config ────────────────────────────────────────────────────
    const STAT_CARDS = [
        {
            title: __('Revenue', 'smartpay'),
            value: loading ? '—' : formatRevenue(periodStats.revenue),
            icon: DollarSign,
        },
        {
            title: __('Completed Orders', 'smartpay'),
            value: loading ? '—' : (periodStats.completed_count || 0),
            icon: CheckCircle2,
        },
        {
            title: __('Pending', 'smartpay'),
            value: loading ? '—' : (periodStats.pending_count || 0),
            icon: Clock,
        },
        {
            title: __('Failed', 'smartpay'),
            value: loading ? '—' : (periodStats.failed_count || 0),
            icon: XCircle,
        },
    ]

    // ─── Chart options ────────────────────────────────────────────────────────
    const chartOptions = {
        chart: {
            type: 'area',
            height: 300,
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true, speed: 600 },
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.02,
                stops: [0, 95, 100],
            },
        },
        colors: ['#3858e9', '#22c55e'],
        xaxis: {
            categories: monthlyChart.map((d) => d.date),
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
            axisBorder: { show: false },
            axisTicks:  { show: false },
        },
        yaxis: {
            labels: {
                formatter: (v) => `${currencySymbol}${Number(v).toLocaleString()}`,
                style: { fontSize: '11px', colors: '#6b7280' },
            },
        },
        grid: {
            borderColor: '#f3f4f6',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '12px',
            markers: { size: 6 },
        },
        tooltip: {
            y: { formatter: (v) => formatRevenue(v) },
        },
    }

    const chartSeries = [
        { name: __('Product Purchase', 'smartpay'), data: monthlyChart.map((d) => d.product_purchase) },
        { name: __('Form Payment', 'smartpay'),     data: monthlyChart.map((d) => d.form_payment) },
    ]

    // ─── Highlights ───────────────────────────────────────────────────────────
    const HIGHLIGHTS = [
        { label: __('Products', 'smartpay'),  value: totals.total_products  || 0, icon: Package },
        { label: __('Forms', 'smartpay'),     value: totals.total_forms     || 0, icon: FileText },
        { label: __('Customers', 'smartpay'), value: totals.total_customers || 0, icon: Users },
    ]

    return (
        <>
            <Header
                title={__('Dashboard', 'smartpay')}
                subtitle={__('Your store at a glance', 'smartpay')}
            />

            <div className="p-4 max-w-7xl mx-auto flex flex-col gap-5">

                {/* ── Top nav: tabs + date range ───────────────────────── */}
                <div className="flex items-center justify-between gap-4 flex-wrap">
                    <div className="flex items-center gap-0.5 rounded-lg border border-border bg-muted/40 p-1 w-fit">
                        {NAV_TABS.map(({ key, label, icon: Icon }) => (
                            <button
                                key={key}
                                type="button"
                                onClick={() => setActiveTab(key)}
                                className={
                                    activeTab === key
                                        ? 'inline-flex items-center gap-1.5 rounded-md px-4 py-1.5 text-sm font-medium bg-background shadow-sm text-foreground transition-all border-0 cursor-pointer'
                                        : 'inline-flex items-center gap-1.5 rounded-md px-4 py-1.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-all bg-transparent border-0 cursor-pointer'
                                }
                            >
                                <Icon className="w-3.5 h-3.5" />
                                {label}
                            </button>
                        ))}
                    </div>

                    <div className="flex items-center gap-2 text-sm text-muted-foreground border border-border rounded-lg px-3 py-1.5 bg-background">
                        <CalendarRange className="w-4 h-4" />
                        <span>{PERIOD_DATE_LABELS[period]?.()}</span>
                    </div>
                </div>

                {/* ── Period filter tabs ────────────────────────────────── */}
                <div className="flex items-center gap-1 rounded-lg border border-border bg-muted/40 p-1 w-fit">
                    {PERIODS.map(({ key, label }) => (
                        <button
                            key={key}
                            type="button"
                            onClick={() => setPeriod(key)}
                            className={
                                period === key
                                    ? 'rounded-md px-4 py-1.5 text-sm font-medium bg-background shadow-sm text-foreground transition-all border-0 cursor-pointer'
                                    : 'rounded-md px-4 py-1.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-all bg-transparent border-0 cursor-pointer'
                            }
                        >
                            {label}
                        </button>
                    ))}
                </div>

                {/* ── Stat cards ────────────────────────────────────────── */}
                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    {STAT_CARDS.map((card) => (
                        <StatCard
                            key={card.title}
                            title={card.title}
                            value={card.value}
                            icon={card.icon}
                        />
                    ))}
                </div>

                {/* ── Chart + Sidebar ───────────────────────────────────── */}
                <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">

                    {/* Chart: 2/3 */}
                    <div className="lg:col-span-2">
                        <Card className="h-full">
                            <CardHeader>
                                <div className="flex items-start justify-between gap-2">
                                    <div>
                                        <CardTitle>{__('Monthly Report', 'smartpay')}</CardTitle>
                                        <CardDescription className="mt-0.5">
                                            {__('Product purchases & form payments', 'smartpay')}
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {loading ? (
                                    <div className="h-[300px] flex items-center justify-center text-muted-foreground text-sm">
                                        {__('Loading chart…', 'smartpay')}
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

                    {/* Sidebar: 1/3 */}
                    <div className="flex flex-col gap-4">

                        {/* Highlights */}
                        <Card>
                            <CardHeader>
                                <CardTitle>{__('Highlights', 'smartpay')}</CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0">
                                <div className="flex flex-col divide-y divide-border">
                                    {HIGHLIGHTS.map(({ label, value, icon: Icon }) => (
                                        <div
                                            key={label}
                                            className="flex items-center justify-between py-3"
                                        >
                                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                <Icon className="w-4 h-4" />
                                                {label}
                                            </div>
                                            <span className="text-sm font-semibold tabular-nums text-card-foreground">
                                                {loading ? '—' : value}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Links */}
                        <Card>
                            <CardHeader>
                                <CardTitle>{__('Quick Links', 'smartpay')}</CardTitle>
                            </CardHeader>
                            <CardContent className="px-3 pb-3 pt-0">
                                <nav className="flex flex-col gap-0.5">
                                    {QUICK_LINKS.map(({ label, icon: Icon, url }) => (
                                        <a
                                            key={label}
                                            href={url}
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

                {/* ── Top Products + Top Forms ──────────────────────────── */}
                <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">

                    <Card>
                        <CardHeader>
                            <CardTitle>{__('Top Products', 'smartpay')}</CardTitle>
                        </CardHeader>
                        <CardContent className="pt-0">
                            {loading ? (
                                <div className="p-4 text-center text-muted-foreground text-sm">
                                    {__('Loading…', 'smartpay')}
                                </div>
                            ) : !topProducts.length ? (
                                <div className="p-4 text-center text-muted-foreground text-sm">
                                    {__('No product sales for this period.', 'smartpay')}
                                </div>
                            ) : (
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-border">
                                            <th className="pb-2.5 text-left font-medium text-muted-foreground">{__('Product', 'smartpay')}</th>
                                            <th className="pb-2.5 text-right font-medium text-muted-foreground">{__('Sales', 'smartpay')}</th>
                                            <th className="pb-2.5 text-right font-medium text-muted-foreground">{__('Revenue', 'smartpay')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topProducts.map((row) => (
                                            <tr key={row.product_id} className="border-b border-border/50 last:border-0">
                                                <td className="py-2.5 text-card-foreground">{row.title || `#${row.product_id}`}</td>
                                                <td className="py-2.5 text-right tabular-nums text-muted-foreground">{row.count}</td>
                                                <td className="py-2.5 text-right tabular-nums font-medium">{formatRevenue(row.total)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{__('Top Forms', 'smartpay')}</CardTitle>
                        </CardHeader>
                        <CardContent className="pt-0">
                            {loading ? (
                                <div className="p-4 text-center text-muted-foreground text-sm">
                                    {__('Loading…', 'smartpay')}
                                </div>
                            ) : !topForms.length ? (
                                <div className="p-4 text-center text-muted-foreground text-sm">
                                    {__('No form payments for this period.', 'smartpay')}
                                </div>
                            ) : (
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-border">
                                            <th className="pb-2.5 text-left font-medium text-muted-foreground">{__('Form', 'smartpay')}</th>
                                            <th className="pb-2.5 text-right font-medium text-muted-foreground">{__('Payments', 'smartpay')}</th>
                                            <th className="pb-2.5 text-right font-medium text-muted-foreground">{__('Revenue', 'smartpay')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topForms.map((row) => (
                                            <tr key={row.form_id} className="border-b border-border/50 last:border-0">
                                                <td className="py-2.5 text-card-foreground">{row.title || `#${row.form_id}`}</td>
                                                <td className="py-2.5 text-right tabular-nums text-muted-foreground">{row.count}</td>
                                                <td className="py-2.5 text-right tabular-nums font-medium">{formatRevenue(row.total)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </CardContent>
                    </Card>

                </div>

            </div>
        </>
    )
}
