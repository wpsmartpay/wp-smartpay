import { Link } from 'react-router-dom'
import { DollarSign, Gift, Users } from 'lucide-react'
import { GetCampaignOverview } from '../../http/campaign'
import { money, count, goalValue, initials, colorIndex, timeAgo } from './utils'

const { __, sprintf } = wp.i18n
const { useState, useEffect } = wp.element

const RANGES = [
    { key: 'today', label: __('Today', 'smartpay') },
    { key: '7d', label: __('7d', 'smartpay') },
    { key: '30d', label: __('30d', 'smartpay') },
    { key: 'all', label: __('All-time', 'smartpay') },
]

// "2026-09" (month bucket) → "Sep 2026"; "2026-09-21" → "Sep 21".
const bucketLabel = (date) => {
    const d = new Date(`${date.length === 7 ? `${date}-01` : date}T00:00:00`)
    if (isNaN(d)) return date
    return date.length === 7
        ? d.toLocaleDateString(undefined, { month: 'short', year: 'numeric' })
        : d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}

/** Minimal bar chart — one bar per day (or month for all-time). */
const RevenueChart = ({ series }) => {
    if (!series.length) {
        return (
            <div className="sp-empty" style={{ padding: 28 }}>
                <div className="sp-empty__title">{__('No gifts in this period', 'smartpay')}</div>
            </div>
        )
    }

    const max = Math.max(...series.map((s) => s.amount), 1)
    const W = 600
    const H = 180
    const gap = 4
    const barW = Math.max(4, Math.min(40, W / series.length - gap))

    return (
        <svg className="sp-campaign-chart" viewBox={`0 0 ${W} ${H + 22}`} role="img" aria-label={__('Revenue by date', 'smartpay')}>
            {series.map((s, i) => {
                const h = Math.max(2, (s.amount / max) * H)
                const x = i * (W / series.length) + (W / series.length - barW) / 2
                return (
                    <g key={s.date}>
                        <rect x={x} y={H - h} width={barW} height={h} rx="3">
                            <title>{`${s.date}: ${money(s.amount)}`}</title>
                        </rect>
                        {(series.length <= 12 || i % Math.ceil(series.length / 8) === 0) && (
                            <text x={x + barW / 2} y={H + 16} textAnchor="middle">{bucketLabel(s.date)}</text>
                        )}
                    </g>
                )
            })}
        </svg>
    )
}

export const OverviewTab = ({ campaign }) => {
    const { StatCard } = window.WPSmartPayUI
    const [range, setRange] = useState('all')
    const [data, setData] = useState(null)

    useEffect(() => {
        let live = true
        setData(null)
        GetCampaignOverview(campaign.id, range).then((d) => live && setData(d)).catch(() => live && setData({ error: true }))
        return () => { live = false }
    }, [campaign.id, range])

    const p = campaign.progress || {}
    const s = data?.stats || {}

    return (
        <div className="sp-stack">
            <div className="sp-campaign-toolbar">
                <span className="sp-detail-card__title">{__('Performance', 'smartpay')}</span>
                <div className="sp-period-selector">
                    {RANGES.map((r) => (
                        <button key={r.key} type="button" onClick={() => setRange(r.key)}
                            className={range === r.key ? 'sp-period-selector__item sp-period-selector__item--active' : 'sp-period-selector__item'}>
                            {r.label}
                        </button>
                    ))}
                </div>
            </div>

            <div className="sp-grid sp-grid--3">
                <StatCard title={__('Amount raised', 'smartpay')} value={data ? money(s.raised) : '…'} icon={DollarSign}
                    change={p.type === 'amount' && p.target > 0 ? sprintf(__('of %s goal', 'smartpay'), money(p.target)) : ''} />
                <StatCard title={__('Donations', 'smartpay')} value={data ? count(s.donations) : '…'} icon={Gift}
                    change={s.donations ? sprintf(__('avg %s', 'smartpay'), money(s.raised / s.donations, 2)) : ''} />
                <StatCard title={__('Donors', 'smartpay')} value={data ? count(s.donors) : '…'} icon={Users}
                    change={data ? sprintf(__('%1$s repeat · %2$s monthly', 'smartpay'), count(s.repeat), count(s.monthly)) : ''} />
            </div>

            <div className="sp-detail-grid">
                <div>
                    <div className="sp-detail-card">
                        <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Revenue', 'smartpay')}</span></div>
                        <div className="sp-detail-card__body">
                            {data ? <RevenueChart series={data.series || []} /> : <div className="sp-state-loading">{__('Loading…', 'smartpay')}</div>}
                        </div>
                    </div>

                    <div className="sp-detail-card">
                        <div className="sp-detail-card__header">
                            <span className="sp-detail-card__title">{__('Recent donations', 'smartpay')}</span>
                            <Link to={`/campaigns/${campaign.id}/donors`} className="sp-campaign-link">{__('View all', 'smartpay')}</Link>
                        </div>
                        <div className="sp-detail-card__body sp-campaign-feed">
                            {!data ? (
                                <div className="sp-state-loading">{__('Loading…', 'smartpay')}</div>
                            ) : !(data.recent || []).length ? (
                                <div className="sp-cell--muted">{__('No donations yet.', 'smartpay')}</div>
                            ) : data.recent.map((g) => (
                                <div key={g.id} className="sp-campaign-feed__row">
                                    <div className="sp-customer">
                                        <div className="sp-avatar" data-color={colorIndex(g.customer.name)}>{initials(g.customer.name)}</div>
                                        <div className="sp-customer__info">
                                            <span className="sp-customer__name">
                                                {g.customer.name || g.customer.email}
                                                {g.anonymous && <span className="sp-badge sp-badge--neutral" style={{ marginLeft: 6 }}>{__('Anonymous', 'smartpay')}</span>}
                                            </span>
                                            <div className="sp-customer__email">{timeAgo(g.created_at)}</div>
                                        </div>
                                    </div>
                                    <span className="sp-cell--num">
                                        {money(g.amount, 2)}{g.monthly && <span className="sp-cell--muted"> · {__('monthly', 'smartpay')}</span>}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div>
                    <div className="sp-detail-card">
                        <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Goal', 'smartpay')}</span></div>
                        <div className="sp-detail-card__body" style={{ textAlign: 'center' }}>
                            {p.target > 0 ? (
                                <>
                                    <div className="sp-campaign-ring" style={{ '--p': Math.min(100, p.percentage || 0) }}>
                                        <span>{Math.round(p.percentage || 0)}%</span>
                                    </div>
                                    <p className="sp-cell--muted" style={{ marginTop: 10 }}>
                                        {sprintf(__('%1$s of %2$s', 'smartpay'), goalValue(p.current, p.type), goalValue(p.target, p.type))}
                                    </p>
                                </>
                            ) : (
                                <p className="sp-cell--muted">{__('No goal set.', 'smartpay')} <Link to={`/campaigns/${campaign.id}/settings`}>{__('Set one', 'smartpay')}</Link></p>
                            )}
                        </div>
                    </div>

                    <div className="sp-detail-card">
                        <div className="sp-detail-card__header">
                            <span className="sp-detail-card__title">{__('Default form', 'smartpay')}</span>
                            <Link to={`/campaigns/${campaign.id}/forms`} className="sp-campaign-link">{__('Change', 'smartpay')}</Link>
                        </div>
                        <div className="sp-detail-card__body">
                            {campaign.default_form_id ? (
                                <p style={{ margin: 0 }}><b>{campaign.default_form_title || `#${campaign.default_form_id}`}</b> ★</p>
                            ) : (
                                <p className="sp-cell--muted" style={{ margin: 0 }}>{__('No forms yet.', 'smartpay')}</p>
                            )}
                            <p className="sp-cell--muted" style={{ margin: '4px 0 0' }}>{__('Used by the “Donate now” button', 'smartpay')}</p>
                        </div>
                    </div>

                    <div className="sp-detail-card">
                        <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Top donors', 'smartpay')}</span></div>
                        <div className="sp-detail-card__body sp-campaign-feed">
                            {!data ? (
                                <div className="sp-state-loading">{__('Loading…', 'smartpay')}</div>
                            ) : !(data.top || []).length ? (
                                <div className="sp-cell--muted">{__('No donors yet.', 'smartpay')}</div>
                            ) : data.top.map((d, i) => (
                                <div key={d.id} className="sp-campaign-feed__row">
                                    <div className="sp-customer">
                                        <span className="sp-campaign-rank">{i + 1}</span>
                                        <div className="sp-avatar" data-color={colorIndex(d.name)}>{initials(d.name)}</div>
                                        <Link to={`/customers/${d.id}`} className="sp-customer__name">{d.name || d.email}</Link>
                                    </div>
                                    <span className="sp-cell--num">{money(d.total)}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}
