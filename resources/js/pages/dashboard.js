import apiFetch from '@wordpress/api-fetch'
import { useEffect, useRef, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import {
    DollarSign,
    CreditCard,
    Activity,
    XCircle,
    Receipt,
    UserCheck,
    FileText,
    Settings,
    Plug,
    BarChart3,
    LineChart,
    HelpCircle,
    ChevronRight,
    Plus,
    RefreshCw,
} from 'lucide-react'
import { Header } from '../components/header'
import { SetupWizard } from '../components/SetupWizard'

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
        label: __('MANAGEMENT', 'smartpay'),
        items: [
            { label: __('Forms', 'smartpay'),          icon: FileText,   hash: '/native-forms' },
            { label: __('Payments', 'smartpay'),       icon: Receipt,    hash: '/payments' },
            { label: __('Subscriptions', 'smartpay'),  icon: RefreshCw,  hash: '/subscriptions' },
            { label: __('Customers', 'smartpay'),      icon: UserCheck,  hash: '/customers' },
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

// ─── Sales Growth Chart ───────────────────────────────────────────────────────
const niceMax = ( v ) => {
    if ( v <= 0 ) return 100
    const mag   = Math.pow( 10, Math.floor( Math.log10( v ) ) )
    const ratio = v / mag
    const nice  = ratio <= 1 ? 1 : ratio <= 2 ? 2 : ratio <= 5 ? 5 : 10
    return nice * mag
}

const niceMaxInt = ( v ) => {
    if ( v <= 0 ) return 4
    if ( v <= 4 ) return 4
    return Math.ceil( v / 4 ) * 4
}

const BRAND = '#293c81'

const SalesGrowthChart = ( { chartData, loading, period } ) => {
    const [chartType, setChartType] = useState( 'area' )
    const [hovered,   setHovered]   = useState( null )

    const W     = 600
    const H     = 230
    const PAD   = { top: 16, right: 48, bottom: 40, left: 64 }
    const plotW = W - PAD.left - PAD.right
    const plotH = H - PAD.top  - PAD.bottom
    const base  = PAD.top + plotH

    const btnStyle = ( active ) => ( {
        background:   active ? 'var(--sp-brand-light)' : 'none',
        border:       '1px solid ' + ( active ? 'var(--sp-brand)' : 'var(--sp-border)' ),
        borderRadius: 5,
        padding:      '3px 6px',
        cursor:       'pointer',
        display:      'flex',
        alignItems:   'center',
        color:        active ? 'var(--sp-brand)' : 'var(--sp-text-muted)',
        transition:   'all .15s',
    } )

    const Header = () => (
        <div className="sp-detail-card__header">
            <span className="sp-detail-card__title">{__( 'Sales Growth', 'smartpay' )}</span>
            <div style={{ marginLeft: 'auto', display: 'flex', gap: 4 }}>
                <button type="button" style={ btnStyle( chartType === 'area' ) }
                        onClick={ () => setChartType( 'area' ) }
                        title={__( 'Line / area', 'smartpay' )}>
                    <LineChart style={{ width: 13, height: 13 }} />
                </button>
                <button type="button" style={ btnStyle( chartType === 'bar' ) }
                        onClick={ () => setChartType( 'bar' ) }
                        title={__( 'Bar chart', 'smartpay' )}>
                    <BarChart3 style={{ width: 13, height: 13 }} />
                </button>
            </div>
        </div>
    )

    if ( loading ) {
        return (
            <div className="sp-detail-card">
                <Header />
                <div className="sp-detail-card__body" style={{ height: 196, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <span style={{ color: 'var(--sp-text-muted)', fontSize: 13 }}>{__( 'Loading…', 'smartpay' )}</span>
                </div>
            </div>
        )
    }

    const data = chartData || []
    const n    = data.length

    if ( n === 0 ) {
        return (
            <div className="sp-detail-card">
                <Header />
                <div className="sp-detail-card__body sp-empty" style={{ height: 160 }}>
                    <div className="sp-empty__icon" style={{ fontSize: 22 }}>📈</div>
                    <div className="sp-empty__title">{__( 'No data yet', 'smartpay' )}</div>
                    <div className="sp-empty__desc">{__( 'Chart will populate once payments are received.', 'smartpay' )}</div>
                </div>
            </div>
        )
    }

    // ── scales ────────────────────────────────────────────────────────────────
    const maxRev = niceMax(    Math.max( ...data.map( d => d.revenue ) ) )
    const maxOrd = niceMaxInt( Math.max( ...data.map( d => d.orders  ) ) )

    const xOf  = ( i ) => PAD.left + ( n === 1 ? plotW / 2 : ( i / ( n - 1 ) ) * plotW )
    const yRev = ( v ) => PAD.top + plotH * ( 1 - v / maxRev )
    const yOrd = ( v ) => PAD.top + plotH * ( 1 - v / maxOrd )

    // ── paths ─────────────────────────────────────────────────────────────────
    const revLine = data.map( ( d, i ) => `${ i === 0 ? 'M' : 'L' }${ xOf(i) },${ yRev(d.revenue) }` ).join( ' ' )
    const revArea = `${ revLine } L${ xOf(n-1) },${ base } L${ xOf(0) },${ base } Z`
    const ordLine = data.map( ( d, i ) => `${ i === 0 ? 'M' : 'L' }${ xOf(i) },${ yOrd(d.orders) }` ).join( ' ' )

    const barW = Math.max( 4, Math.min( 18, ( plotW / n ) * 0.55 ) )

    // ── ticks & labels ────────────────────────────────────────────────────────
    const ticks = [ 0, 0.25, 0.5, 0.75, 1 ]
    const step  = n <= 7 ? 1 : n <= 14 ? 2 : Math.ceil( n / 6 )

    // ── tooltip ───────────────────────────────────────────────────────────────
    const tip  = hovered !== null ? data[ hovered ] : null
    const tipX = tip ? Math.min( xOf( hovered ) + 10, W - 110 ) : 0
    const tipY = PAD.top + 4

    return (
        <div className="sp-detail-card">
            <Header />
            <div className="sp-detail-card__body" style={{ paddingTop: 4, paddingBottom: 0 }}>

                {/* Legend */}
                <div style={{ display: 'flex', gap: 14, justifyContent: 'center', marginBottom: 2, fontSize: 11, color: 'var(--sp-text-muted)' }}>
                    <span style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                        <span style={{ width: 8, height: 8, borderRadius: '50%', background: BRAND, opacity: 0.45, display: 'inline-block', flexShrink: 0 }} />
                        {__( 'Revenue', 'smartpay' )}
                    </span>
                    <span style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                        <span style={{ width: 8, height: 8, borderRadius: '50%', background: BRAND, display: 'inline-block', flexShrink: 0 }} />
                        {__( 'Orders', 'smartpay' )}
                    </span>
                </div>

                <svg
                    viewBox={ `0 0 ${ W } ${ H }` }
                    style={{ width: '100%', display: 'block' }}
                    onMouseLeave={ () => setHovered( null ) }
                >
                    <defs>
                        <linearGradient id="sp-rev-grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stopColor={ BRAND } stopOpacity="0.16" />
                            <stop offset="100%" stopColor={ BRAND } stopOpacity="0.01" />
                        </linearGradient>
                    </defs>

                    {/* Dashed grid lines */}
                    { ticks.map( ( t, i ) => (
                        <line key={ i }
                              x1={ PAD.left } y1={ yRev( maxRev * t ) }
                              x2={ PAD.left + plotW } y2={ yRev( maxRev * t ) }
                              stroke="#e5e7eb" strokeWidth="1" strokeDasharray="4 3" />
                    ) ) }

                    {/* Revenue Y-axis labels (left) */}
                    { ticks.map( ( t, i ) => (
                        <text key={ i } x={ PAD.left - 6 } y={ yRev( maxRev * t ) + 4 }
                              textAnchor="end" fontSize="9.5" fill="#aaa">
                            { t === 0 ? '0' : `$${ ( maxRev * t ).toLocaleString( undefined, { maximumFractionDigits: 0 } ) }` }
                        </text>
                    ) ) }

                    {/* Orders Y-axis labels (right) */}
                    { ticks.map( ( t, i ) => (
                        <text key={ i } x={ PAD.left + plotW + 6 } y={ yOrd( maxOrd * t ) + 4 }
                              textAnchor="start" fontSize="9.5" fill="#aaa">
                            { Math.round( maxOrd * t ) }
                        </text>
                    ) ) }

                    {/* Y-axis titles */}
                    <text x="9" y={ PAD.top + plotH / 2 }
                          textAnchor="middle" fontSize="9" fill="#c3c3c3"
                          transform={ `rotate(-90, 9, ${ PAD.top + plotH / 2 })` }>
                        { __( 'Revenue', 'smartpay' ) }
                    </text>
                    <text x={ W - 9 } y={ PAD.top + plotH / 2 }
                          textAnchor="middle" fontSize="9" fill="#c3c3c3"
                          transform={ `rotate(90, ${ W - 9 }, ${ PAD.top + plotH / 2 })` }>
                        { __( 'Orders', 'smartpay' ) }
                    </text>

                    {/* Revenue: area or bars */}
                    { chartType === 'area' ? (
                        <>
                            <path d={ revArea } fill="url(#sp-rev-grad)" />
                            <path d={ revLine } fill="none" stroke={ BRAND } strokeWidth="1.8" strokeOpacity="0.65" />
                        </>
                    ) : (
                        data.map( ( d, i ) => (
                            <rect key={ i }
                                  x={ xOf(i) - barW / 2 } y={ yRev( d.revenue ) }
                                  width={ barW } height={ base - yRev( d.revenue ) }
                                  fill={ BRAND } fillOpacity="0.3" rx="2" />
                        ) )
                    ) }

                    {/* Orders line + dots */}
                    <path d={ ordLine } fill="none" stroke={ BRAND } strokeWidth="1.8" />
                    { data.map( ( d, i ) => (
                        <circle key={ i } cx={ xOf(i) } cy={ yOrd( d.orders ) }
                                r="2.8" fill="white" stroke={ BRAND } strokeWidth="1.6" />
                    ) ) }

                    {/* X-axis labels */}
                    { data.map( ( d, i ) => {
                        if ( i % step !== 0 && i !== n - 1 ) return null
                        return (
                            <text key={ i } x={ xOf(i) } y={ base + 16 }
                                  textAnchor="middle" fontSize="9.5" fill="#aaa">
                                { d.label }
                            </text>
                        )
                    } ) }

                    {/* Invisible hover columns */}
                    { data.map( ( _, i ) => (
                        <rect key={ i }
                              x={ xOf(i) - ( n === 1 ? plotW / 2 : plotW / ( 2 * ( n - 1 ) ) ) }
                              y={ PAD.top } width={ n === 1 ? plotW : plotW / ( n - 1 ) } height={ plotH }
                              fill="transparent"
                              onMouseEnter={ () => setHovered( i ) } />
                    ) ) }

                    {/* Hover vertical line + enlarged dots */}
                    { hovered !== null && (
                        <>
                            <line x1={ xOf( hovered ) } y1={ PAD.top }
                                  x2={ xOf( hovered ) } y2={ base }
                                  stroke={ BRAND } strokeWidth="1"
                                  strokeDasharray="3 2" strokeOpacity="0.45" />
                            <circle cx={ xOf( hovered ) } cy={ yRev( data[ hovered ].revenue ) }
                                    r="5" fill={ BRAND } fillOpacity="0.75" stroke="white" strokeWidth="2" />
                            <circle cx={ xOf( hovered ) } cy={ yOrd( data[ hovered ].orders ) }
                                    r="5" fill={ BRAND } stroke="white" strokeWidth="2" />
                        </>
                    ) }

                    {/* Tooltip */}
                    { tip && (
                        <g>
                            <rect x={ tipX } y={ tipY } width="104" height="52"
                                  rx="6" fill="white" stroke="#e5e7eb" strokeWidth="1"
                                  style={{ filter: 'drop-shadow(0 2px 8px rgba(0,0,0,.08))' }} />
                            <text x={ tipX + 9 } y={ tipY + 15 } fontSize="9.5" fill="#888">{ tip.label }</text>
                            <text x={ tipX + 9 } y={ tipY + 31 } fontSize="11" fontWeight="600" fill={ BRAND }>
                                { formatRevenue( tip.revenue ) }
                            </text>
                            <text x={ tipX + 9 } y={ tipY + 45 } fontSize="9.5" fill="#888">
                                { tip.orders } { tip.orders === 1
                                    ? __( 'order', 'smartpay' )
                                    : __( 'orders', 'smartpay' ) }
                            </text>
                        </g>
                    ) }

                </svg>
            </div>
        </div>
    )
}

// ─── Onboarding Progress Card ─────────────────────────────────────────────────
const ONBOARDING_ITEMS = [
    { id: 1, label: __( 'Configure currency & settings', 'smartpay' ) },
    { id: 2, label: __( 'Connect a payment gateway', 'smartpay' ) },
    { id: 3, label: __( 'Create a product or form', 'smartpay' ) },
    { id: 4, label: __( 'Receive your first payment', 'smartpay' ) },
]

const loadOnboardingChecked = () => {
    try { return JSON.parse( localStorage.getItem( 'sp_onboarding_v1' ) || '{}' ) } catch { return {} }
}

const CheckCircle = ( { done } ) => done ? (
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style={{ flexShrink: 0 }}>
        <circle cx="10" cy="10" r="10" fill="var(--sp-brand)" />
        <path d="M6 10.5l2.8 2.8 5.2-5.8" stroke="#fff" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
) : (
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style={{ flexShrink: 0 }}>
        <circle cx="10" cy="10" r="9" stroke="#c3c4c7" strokeWidth="1.5" strokeDasharray="3 2" />
    </svg>
)

const OnboardingProgressCard = ( { hasPayments, onLaunchWizard } ) => {
    const [checked, setChecked] = useState( () => loadOnboardingChecked() )

    const isChecked = ( id ) => id === 4 ? hasPayments : !! checked[ id ]
    const doneCount = ONBOARDING_ITEMS.filter( ( item ) => isChecked( item.id ) ).length
    const allDone   = doneCount === 4

    return (
        <div className="sp-detail-card" style={{ overflow: 'hidden' }}>
            <div className="sp-detail-card__header">
                <span className="sp-detail-card__title">{__( 'GETTING STARTED', 'smartpay' )}</span>
                <span style={{
                    marginLeft: 'auto',
                    fontSize: 11,
                    fontWeight: 700,
                    background: allDone ? 'var(--sp-brand)' : 'var(--sp-brand-light)',
                    color: allDone ? '#fff' : 'var(--sp-brand)',
                    padding: '2px 8px',
                    borderRadius: 99,
                }}>
                    {doneCount} / 4
                </span>
            </div>

            {/* Progress bar */}
            <div style={{ height: 3, background: 'var(--sp-border)', marginBottom: 0 }}>
                <div style={{
                    height: '100%',
                    width: `${( doneCount / 4 ) * 100}%`,
                    background: 'var(--sp-brand)',
                    transition: 'width .3s ease',
                }} />
            </div>

            <div className="sp-detail-card__body" style={{ paddingTop: 10, paddingBottom: 0 }}>
                {allDone ? (
                    <div style={{ padding: '8px 0 10px', textAlign: 'center' }}>
                        <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--sp-brand)', marginBottom: 3 }}>
                            {__( "You're all set!", 'smartpay' )}
                        </div>
                        <div style={{ fontSize: 11.5, color: 'var(--sp-text-muted)' }}>
                            {__( 'All setup steps complete.', 'smartpay' )}
                        </div>
                    </div>
                ) : (
                    <ul style={{ listStyle: 'none', margin: '0 0 4px', padding: 0 }}>
                        {ONBOARDING_ITEMS.map( ( item, i ) => {
                            const done = isChecked( item.id )
                            return (
                                <li key={item.id} style={{
                                    display:    'flex',
                                    alignItems: 'center',
                                    gap:        10,
                                    padding:    '6px 0',
                                    borderTop:  i > 0 ? '1px solid var(--sp-border)' : 'none',
                                }}>
                                    <CheckCircle done={done} />
                                    <span style={{
                                        fontSize:       12.5,
                                        fontWeight:     done ? 400 : 500,
                                        color:          done ? 'var(--sp-text-muted)' : 'var(--sp-text)',
                                        textDecoration: done ? 'line-through' : 'none',
                                        flex:           1,
                                        minWidth:       0,
                                    }}>
                                        {item.label}
                                    </span>
                                    {done && (
                                        <span style={{ fontSize: 10, color: 'var(--sp-brand)', fontWeight: 600, flexShrink: 0 }}>
                                            {__( 'Done', 'smartpay' )}
                                        </span>
                                    )}
                                </li>
                            )
                        } )}
                    </ul>
                )}
            </div>

            {! allDone && onLaunchWizard && (
                <div style={{ padding: '8px 20px 14px', borderTop: '1px solid var(--sp-border)' }}>
                    <button
                        type="button"
                        onClick={onLaunchWizard}
                        style={{ background: 'none', border: 'none', padding: 0, cursor: 'pointer', fontSize: 12, color: 'var(--sp-brand)', fontWeight: 600 }}
                        onMouseOver={( e ) => e.currentTarget.style.textDecoration = 'underline'}
                        onMouseOut={( e )  => e.currentTarget.style.textDecoration = 'none'}
                    >
                        {__( 'Run Setup Wizard →', 'smartpay' )}
                    </button>
                </div>
            )}
        </div>
    )
}

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
    const [wizardOpen, setWizardOpen]    = useState(
        () => ! localStorage.getItem('sp_wizard_v1_shown')
    )

    useEffect(() => {
        setStatsLoad(true)
        apiFetch({
            url:     buildUrl(period),
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

                {/* ── 4 stat cards ─────────────────────────────────────────── */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 20 }}>
                    <StatCard icon={DollarSign} label={__('Total Revenue', 'smartpay')}       value={formatRevenue(curr.revenue)}    loading={statsLoading} />
                    <StatCard icon={CreditCard} label={__('Completed Payments', 'smartpay')}  value={curr.completed_count ?? 0}      loading={statsLoading} />
                    <StatCard icon={Activity}   label={__('Pending', 'smartpay')}             value={curr.pending_count ?? 0}        loading={statsLoading} />
                    <StatCard icon={XCircle}    label={__('Failed', 'smartpay')}              value={curr.failed_count ?? 0}         loading={statsLoading} />
                </div>

                {/* ── Two columns: Left (payments + nav) | Right (CTAs + checklist) ── */}
                <div style={{ display: 'grid', gridTemplateColumns: '3fr 1fr', gap: 20, alignItems: 'start' }}>

                    {/* ── LEFT: Chart + Recent Payments + Navigation ───────────── */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>

                        <SalesGrowthChart
                            chartData={ data?.chart_data }
                            loading={ statsLoading }
                            period={ period }
                        />

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

                        {/* Navigation — Management + Configuration */}
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

                    {/* ── RIGHT: CTAs + Onboarding Checklist card ──────────────── */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>

                        {/* Quick actions card */}
                        <div className="sp-detail-card">
                            <div className="sp-detail-card__header">
                                <span className="sp-detail-card__title">{__( 'QUICK ACTIONS', 'smartpay' )}</span>
                            </div>
                            <div className="sp-detail-card__body" style={{ display: 'flex', flexDirection: 'column', gap: 8, padding: '12px 16px' }}>
                                <a
                                    href={ `${ adminUrl }?page=smartpay#/native-forms` }
                                    className="sp-btn sp-btn--primary"
                                    style={{ textDecoration: 'none', justifyContent: 'center', fontSize: 12, height: 32 }}
                                >
                                    {__( '+ Create Payment Form', 'smartpay' )}
                                </a>
                                <a
                                    href={ `${ adminUrl }?page=smartpay#/native-forms` }
                                    className="sp-btn sp-btn--outline"
                                    style={{ textDecoration: 'none', justifyContent: 'center', fontSize: 12, height: 32 }}
                                >
                                    {__( 'Forms', 'smartpay' )}
                                </a>
                                <a
                                    href={ `${ adminUrl }?page=smartpay-integrations` }
                                    className="sp-btn sp-btn--outline"
                                    style={{ textDecoration: 'none', justifyContent: 'center', fontSize: 12, height: 32 }}
                                >
                                    {__( 'Integrations', 'smartpay' )}
                                </a>
                            </div>
                        </div>

                        {/* Onboarding Checklist summary card */}
                        <OnboardingProgressCard
                            hasPayments={( curr.completed_count || 0 ) > 0}
                            onLaunchWizard={() => setWizardOpen( true )}
                        />

                    </div>

                </div>


            </div>

            <SetupWizard
                isOpen={wizardOpen}
                onClose={() => setWizardOpen( false )}
            />
        </>
    )
}
