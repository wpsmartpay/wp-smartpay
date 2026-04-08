import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Header } from '../components/header'
import { Report } from '../components/report/report'

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

const PERIODS = [
    { key: 'today', label: __('Today', 'smartpay') },
    { key: 'week', label: __('Week to date', 'smartpay') },
    { key: 'month', label: __('Month to date', 'smartpay') },
]

// apiFetch in this plugin is NOT configured with the WP REST root URL middleware.
// Relative paths get prepended with the site origin — not the REST root — and break.
// Solution: build the full URL from window.smartpay.restUrl (already permalink-mode-aware from PHP),
// then add query params via the URL API so they don't corrupt the ?rest_route= value.
//
// Non-pretty:  http://site.com/index.php?rest_route=%2Fsmartpay%2Fv1%2Fdashboard&period=today
// Pretty:      http://site.com/wp-json/smartpay/v1/dashboard?period=today
const buildDashboardUrl = (period) => {
    const url = new URL(`${window.smartpay.restUrl}/v1/dashboard`)
    url.searchParams.set('period', period)
    return url.toString()
}

const QUICK_LINKS = [
    {
        label: __('Products', 'smartpay'),
        icon: 'dashicons-products',
        url: `${adminUrl}?page=smartpay#/products`,
    },
    {
        label: __('Forms', 'smartpay'),
        icon: 'dashicons-feedback',
        url: `${adminUrl}?page=smartpay-form`,
    },
    {
        label: __('Members', 'smartpay'),
        icon: 'dashicons-admin-users',
        url: `${adminUrl}?page=smartpay#/members`,
    },
    {
        label: __('Payments', 'smartpay'),
        icon: 'dashicons-cart',
        url: `${adminUrl}?page=smartpay#/payments`,
    },
    {
        label: __('Coupons', 'smartpay'),
        icon: 'dashicons-tag',
        url: `${adminUrl}?page=smartpay#/coupons`,
    },
    {
        label: __('Settings', 'smartpay'),
        icon: 'dashicons-admin-settings',
        url: `${adminUrl}?page=smartpay-setting`,
    },
]

export const Dashboard = () => {
    const [period, setPeriod] = useState('month')
    const [data, setData] = useState(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        setLoading(true)
        apiFetch({
            path: buildDashboardUrl(period),
            headers: { 'X-WP-Nonce': apiNonce },
        })
            .then((response) => setData(response))
            .finally(() => setLoading(false))
    }, [period])

    const periodStats = data?.period_stats || {}
    const totals = data?.totals || {}
    const topProducts = data?.top_products || []
    const topForms = data?.top_forms || []
    const monthlyChart = data?.monthly_chart || []

    const formatRevenue = (amount) =>
        `${currencySymbol}${Number(amount || 0).toFixed(2)}`

    return (
        <>
            <Header
                title={__('Dashboard', 'smartpay')}
                subtitle={__('Your store at a glance', 'smartpay')}
            />

            <div className="smartpay-dashboard">

                {/* ── Stats Overview ── */}
                <div className="smartpay-dashboard__section smartpay-dashboard__section--full">
                    <div className="smartpay-dashboard__card">

                        <div className="smartpay-stats-header">
                            <h3 className="smartpay-stats-header__title">
                                {__('Stats Overview', 'smartpay')}
                            </h3>
                            <div
                                className="smartpay-period-tabs"
                                role="tablist"
                                aria-label={__('Stats period', 'smartpay')}
                            >
                                {PERIODS.map(({ key, label }) => (
                                    <button
                                        key={key}
                                        type="button"
                                        role="tab"
                                        aria-selected={period === key}
                                        className={`smartpay-period-tab${period === key ? ' is-active' : ''}`}
                                        onClick={() => setPeriod(key)}
                                    >
                                        {label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="smartpay-stats-grid">
                            <div className="smartpay-stat-tile smartpay-stat-tile--revenue">
                                <span className="dashicons dashicons-chart-line smartpay-stat-tile__icon" aria-hidden="true"></span>
                                <div className="smartpay-stat-tile__body">
                                    <span className="smartpay-stat-tile__value">
                                        {loading ? '–' : formatRevenue(periodStats.revenue)}
                                    </span>
                                    <span className="smartpay-stat-tile__label">
                                        {__('Revenue', 'smartpay')}
                                    </span>
                                </div>
                            </div>

                            <div className="smartpay-stat-tile smartpay-stat-tile--completed">
                                <span className="dashicons dashicons-yes-alt smartpay-stat-tile__icon" aria-hidden="true"></span>
                                <div className="smartpay-stat-tile__body">
                                    <span className="smartpay-stat-tile__value">
                                        {loading ? '–' : (periodStats.completed_count || 0)}
                                    </span>
                                    <span className="smartpay-stat-tile__label">
                                        {__('Completed Orders', 'smartpay')}
                                    </span>
                                </div>
                            </div>

                            <div className="smartpay-stat-tile smartpay-stat-tile--pending">
                                <span className="dashicons dashicons-clock smartpay-stat-tile__icon" aria-hidden="true"></span>
                                <div className="smartpay-stat-tile__body">
                                    <span className="smartpay-stat-tile__value">
                                        {loading ? '–' : (periodStats.pending_count || 0)}
                                    </span>
                                    <span className="smartpay-stat-tile__label">
                                        {__('Pending', 'smartpay')}
                                    </span>
                                </div>
                            </div>

                            <div className="smartpay-stat-tile smartpay-stat-tile--failed">
                                <span className="dashicons dashicons-dismiss smartpay-stat-tile__icon" aria-hidden="true"></span>
                                <div className="smartpay-stat-tile__body">
                                    <span className="smartpay-stat-tile__value">
                                        {loading ? '–' : (periodStats.failed_count || 0)}
                                    </span>
                                    <span className="smartpay-stat-tile__label">
                                        {__('Failed', 'smartpay')}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="smartpay-totals-row">
                            <span className="smartpay-totals-row__item">
                                <span className="dashicons dashicons-products" aria-hidden="true"></span>
                                <strong>{totals.total_products || 0}</strong>
                                &nbsp;{__('Products', 'smartpay')}
                            </span>
                            <span className="smartpay-totals-row__item">
                                <span className="dashicons dashicons-feedback" aria-hidden="true"></span>
                                <strong>{totals.total_forms || 0}</strong>
                                &nbsp;{__('Forms', 'smartpay')}
                            </span>
                            <span className="smartpay-totals-row__item">
                                <span className="dashicons dashicons-admin-users" aria-hidden="true"></span>
                                <strong>{totals.total_customers || 0}</strong>
                                &nbsp;{__('Customers', 'smartpay')}
                            </span>
                        </div>

                    </div>
                </div>

                {/* ── Monthly Chart + Quick Links ── */}
                <div className="smartpay-dashboard__grid">
                    <div className="smartpay-dashboard__chart">
                        <div className="smartpay-dashboard__section">
                            <h2 className="smartpay-dashboard__section-title">
                                {__('Monthly Report', 'smartpay')}
                            </h2>
                            <div className="smartpay-dashboard__card">
                                <Report
                                    height="350"
                                    series={[
                                        {
                                            name: __('Product Purchase', 'smartpay'),
                                            data: monthlyChart.map((d) => d.product_purchase),
                                        },
                                        {
                                            name: __('Form Payment', 'smartpay'),
                                            data: monthlyChart.map((d) => d.form_payment),
                                        },
                                    ]}
                                    options={{
                                        chart: {
                                            type: 'bar',
                                            height: 350,
                                            stacked: true,
                                            toolbar: { show: true },
                                        },
                                        plotOptions: {
                                            bar: {
                                                horizontal: false,
                                                columnWidth: '60%',
                                                borderRadius: 4,
                                            },
                                        },
                                        colors: ['#3858e9', '#22c55e'],
                                        dataLabels: { enabled: false },
                                        xaxis: { categories: monthlyChart.map((d) => d.date) },
                                        yaxis: { title: { text: __('Revenue', 'smartpay') } },
                                        legend: { position: 'bottom', offsetY: 10 },
                                        fill: { opacity: 1 },
                                        grid: { borderColor: '#e5e7eb' },
                                    }}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Quick Links in sidebar */}
                    <div className="smartpay-dashboard__sidebar">
                        <div className="smartpay-dashboard__section">
                            <h2 className="smartpay-dashboard__section-title">
                                {__('Quick Links', 'smartpay')}
                            </h2>
                            <div className="smartpay-dashboard__card">
                                <nav
                                    className="smartpay-quick-links"
                                    aria-label={__('Quick navigation', 'smartpay')}
                                >
                                    {QUICK_LINKS.map(({ label, icon, url }) => (
                                        <a key={label} href={url} className="smartpay-quick-link">
                                            <span
                                                className={`dashicons ${icon} smartpay-quick-link__icon`}
                                                aria-hidden="true"
                                            ></span>
                                            <span className="smartpay-quick-link__label">{label}</span>
                                        </a>
                                    ))}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Top Products + Top Forms ── */}
                <div className="smartpay-dashboard__grid smartpay-dashboard__grid--equal">
                    <div className="smartpay-dashboard__section">
                        <h2 className="smartpay-dashboard__section-title">
                            {__('Top Products', 'smartpay')}
                        </h2>
                        <div className="smartpay-dashboard__card">
                            {loading ? (
                                <p className="smartpay-loading-text">{__('Loading…', 'smartpay')}</p>
                            ) : !topProducts.length ? (
                                <p className="smartpay-empty-notice">
                                    {__('No product sales for this period.', 'smartpay')}
                                </p>
                            ) : (
                                <table className="smartpay-top-table">
                                    <thead>
                                        <tr>
                                            <th>{__('Product', 'smartpay')}</th>
                                            <th>{__('Sales', 'smartpay')}</th>
                                            <th>{__('Revenue', 'smartpay')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topProducts.map((row) => (
                                            <tr key={row.product_id}>
                                                <td>{row.title || `#${row.product_id}`}</td>
                                                <td>{row.count}</td>
                                                <td>{formatRevenue(row.total)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>

                    <div className="smartpay-dashboard__section">
                        <h2 className="smartpay-dashboard__section-title">
                            {__('Top Forms', 'smartpay')}
                        </h2>
                        <div className="smartpay-dashboard__card">
                            {loading ? (
                                <p className="smartpay-loading-text">{__('Loading…', 'smartpay')}</p>
                            ) : !topForms.length ? (
                                <p className="smartpay-empty-notice">
                                    {__('No form payments for this period.', 'smartpay')}
                                </p>
                            ) : (
                                <table className="smartpay-top-table">
                                    <thead>
                                        <tr>
                                            <th>{__('Form', 'smartpay')}</th>
                                            <th>{__('Payments', 'smartpay')}</th>
                                            <th>{__('Revenue', 'smartpay')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topForms.map((row) => (
                                            <tr key={row.form_id}>
                                                <td>{row.title || `#${row.form_id}`}</td>
                                                <td>{row.count}</td>
                                                <td>{formatRevenue(row.total)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>
                </div>

            </div>
        </>
    )
}
