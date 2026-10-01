import { Link, useParams } from 'react-router-dom'
import { MessageSquare } from 'lucide-react'
import { GetDonor } from '../../http/campaign'
import { money, count, colorIndex, initials, shortDate, DonorTypeBadge } from '../campaign/utils'

const { __, sprintf } = wp.i18n
const { useState, useEffect, useCallback } = wp.element

const STATUS_CLASS = {
    completed: 'sp-badge--active',
    pending: 'sp-badge--pending',
    refunded: 'sp-badge--expired',
    failed: 'sp-badge--failed',
}

export const ShowDonor = () => {
    const { Header, StatCard } = window.WPSmartPayUI
    const { donorId } = useParams()
    const [data, setData] = useState(null)
    const [error, setError] = useState(false)

    const load = useCallback(() => {
        GetDonor(donorId).then(setData).catch(() => setError(true))
    }, [donorId])

    useEffect(() => { load() }, [load])

    if (error) {
        return (
            <div className="sp-layout">
                <Link to="/donors" className="sp-back-btn"><span className="sp-back-btn__arrow">←</span>{__('All Donors', 'smartpay')}</Link>
                <div className="sp-empty"><div className="sp-empty__title">{__('Donor not found', 'smartpay')}</div></div>
            </div>
        )
    }
    if (!data) return <div className="sp-layout"><div className="sp-state-loading">{__('Loading…', 'smartpay')}</div></div>

    const { donor, customer, stats, history, campaigns, wall } = data
    const name = donor.name || customer.email
    const st = stats.status || {}
    // Pro adds side cards (e.g. private notes): [{ key, element }].
    const sideCards = window.wp?.hooks?.applyFilters?.('smartpay_donor_detail_cards', [], data, load) || []

    return (
        <>
            <Header title={name} subtitle={__('Donor', 'smartpay')} />
            <div className="sp-layout">
                <Link to="/donors" className="sp-back-btn"><span className="sp-back-btn__arrow">←</span>{__('All Donors', 'smartpay')}</Link>

                <div className="sp-page-title__inner sp-campaign-head">
                    <div className="sp-customer">
                        <div className="sp-avatar" data-color={colorIndex(name)} style={{ width: 44, height: 44 }}>{initials(name)}</div>
                        <div>
                            <h1 className="sp-page-title__heading" style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                                {name} <DonorTypeBadge type={donor.type} monthlyAmount={donor.monthly_amount} />
                            </h1>
                            <p className="sp-page-title__sub">
                                {customer.email} · #{customer.id} · {sprintf(__('Donor since %s', 'smartpay'), shortDate(donor.donor_since))}
                            </p>
                        </div>
                    </div>
                    <Link to={`/customers/${customer.id}`} className="sp-btn sp-btn--outline">{__('Customer record', 'smartpay')}</Link>
                </div>

                <div className="sp-grid sp-grid--4" style={{ marginBottom: 20 }}>
                    <StatCard title={__('Lifetime given', 'smartpay')} value={money(stats.lifetime, 2)} />
                    <StatCard title={__('Gifts', 'smartpay')} value={count(stats.gifts)} />
                    <StatCard title={__('Average gift', 'smartpay')} value={money(stats.average, 2)} />
                    <StatCard title={__('Largest gift', 'smartpay')} value={money(stats.largest, 2)} />
                </div>

                <p className="sp-cell--muted" style={{ margin: '0 0 16px' }}>
                    {sprintf(
                        __('Payments: %1$d total · %2$d completed · %3$d pending · %4$d refunded', 'smartpay'),
                        history.length, st.completed || 0, st.pending || 0, st.refunded || 0
                    )}
                </p>

                <div className="sp-detail-grid">
                    <div>
                        <div className="sp-detail-card">
                            <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Giving history', 'smartpay')}</span></div>
                            <table className="sp-table">
                                <thead>
                                    <tr>
                                        <th>{__('ID', 'smartpay')}</th>
                                        <th>{__('Campaign / Form', 'smartpay')}</th>
                                        <th>{__('Frequency', 'smartpay')}</th>
                                        <th>{__('Date', 'smartpay')}</th>
                                        <th>{__('Status', 'smartpay')}</th>
                                        <th>{__('Amount', 'smartpay')}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {history.map((g) => (
                                        <tr key={g.id}>
                                            <td><Link to={`/payments/${g.id}`}>#{g.id}</Link></td>
                                            <td>
                                                <b>{g.campaign ? g.campaign.title : __('No campaign', 'smartpay')}</b>
                                                <div className="sp-cell--muted">{g.form}</div>
                                            </td>
                                            <td>{g.frequency === 'monthly' ? __('Monthly', 'smartpay') : __('One-time', 'smartpay')}</td>
                                            <td className="sp-cell--muted sp-col--nowrap">{shortDate(g.created_at)}</td>
                                            <td><span className={`sp-badge sp-badge--dot ${STATUS_CLASS[g.status] || 'sp-badge--expired'}`}>{g.status}</span></td>
                                            <td className="sp-cell--num" style={{ textAlign: 'left' }}>{money(g.amount, 2)}</td>
                                            <td>
                                                {g.comment && <span title={g.comment}><MessageSquare size={14} /></span>}
                                                {g.anonymous && <span className="sp-badge sp-badge--neutral" style={{ marginLeft: 4 }}>{__('Anonymous', 'smartpay')}</span>}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div className="sp-detail-card">
                            <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Campaigns supported', 'smartpay')}</span></div>
                            <div className="sp-detail-card__body sp-campaign-feed">
                                {!campaigns.length && <span className="sp-cell--muted">{__('Gifts on donation forms without a campaign.', 'smartpay')}</span>}
                                {campaigns.map((c) => (
                                    <div key={c.id} className="sp-campaign-feed__row">
                                        <div>
                                            <Link to={`/campaigns/${c.id}`} className="sp-customer__name">{c.title}</Link>
                                            <div className="sp-customer__email">
                                                {sprintf(__('%1$d gifts · last %2$s', 'smartpay'), c.gifts, shortDate(c.latest))}
                                            </div>
                                        </div>
                                        <span className="sp-cell--num">{money(c.total)}</span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="sp-detail-card">
                            <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Public wall', 'smartpay')}</span></div>
                            <div className="sp-detail-card__body">
                                <table className="sp-kv-table">
                                    <tbody>
                                        <tr><td>{__('Shown as', 'smartpay')}</td><td>{wall.name}</td></tr>
                                        <tr><td>{__('Donor hid name', 'smartpay')}</td><td>{wall.hide_name ? __('Yes', 'smartpay') : __('No', 'smartpay')}</td></tr>
                                    </tbody>
                                </table>
                                <p className="sp-cell--muted" style={{ margin: '8px 0 0' }}>{__('Only used when a campaign’s donor list is public.', 'smartpay')}</p>
                            </div>
                        </div>

                        {sideCards.map((c) => <div key={c.key}>{c.element}</div>)}
                    </div>
                </div>
            </div>
        </>
    )
}
