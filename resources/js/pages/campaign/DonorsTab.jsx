import { Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import { GetCampaignDonors } from '../../http/campaign'
import { money, count, colorIndex, initials, shortDate, DonorTypeBadge, DONOR_TYPES } from './utils'

const { __, sprintf } = wp.i18n
const { useState, useEffect, useCallback } = wp.element

const FLAGS = [
    { key: '', label: __('All', 'smartpay') },
    { key: 'comments', label: __('With comments', 'smartpay') },
    { key: 'anonymous', label: __('Anonymous', 'smartpay') },
]

export const DonorsTab = ({ campaign }) => {
    const [search, setSearch] = useState('')
    const [debounced, setDebounced] = useState('')
    const [type, setType] = useState('')
    const [flag, setFlag] = useState('')
    const [result, setResult] = useState(null)

    // Pro: extra columns [{ key, label, render(donor, reload) }] and the visibility line.
    const hooks = window.wp?.hooks
    const extraColumns = hooks?.applyFilters?.('smartpay_campaign_donor_columns', [], campaign) || []
    const visibility = hooks?.applyFilters?.('smartpay_campaign_donor_visibility', null, campaign)

    useEffect(() => {
        const t = setTimeout(() => setDebounced(search), 400)
        return () => clearTimeout(t)
    }, [search])

    const load = useCallback((page = 1) => {
        setResult(null)
        GetCampaignDonors(campaign.id, { page, per_page: 20, search: debounced, type, flag })
            .then(setResult)
            .catch(() => setResult({ data: [], total: 0 }))
    }, [campaign.id, debounced, type, flag])

    useEffect(() => { load(1) }, [load])

    const rows = result?.data || []
    const colSpan = 7 + extraColumns.length

    return (
        <div className="sp-stack">
            <p className="sp-campaign-info">
                {visibility || (
                    <>
                        <b>{__('Donor list:', 'smartpay')}</b> {__('Private — only admins see this list. The campaign page shows counts only, never names.', 'smartpay')}
                    </>
                )}
            </p>

            <div className="sp-toolbar" style={{ marginBottom: 0 }}>
                <div className="sp-search">
                    <Search className="sp-search__icon" size={14} />
                    <input type="search" className="sp-search__input" placeholder={__('Search donors', 'smartpay')}
                        value={search} onChange={(e) => setSearch(e.target.value)} />
                </div>
                <select className="sp-filter-select" value={type} onChange={(e) => setType(e.target.value)} aria-label={__('Donor type', 'smartpay')}>
                    <option value="">{__('All types', 'smartpay')}</option>
                    {Object.entries(DONOR_TYPES).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
                </select>
                <div className="sp-period-selector">
                    {FLAGS.map((f) => (
                        <button key={f.key} type="button" onClick={() => setFlag(f.key)}
                            className={flag === f.key ? 'sp-period-selector__item sp-period-selector__item--active' : 'sp-period-selector__item'}>
                            {f.label}
                        </button>
                    ))}
                </div>
            </div>

            <div className="sp-table-card">
                <table className="sp-table">
                    <thead>
                        <tr>
                            <th>{__('Donor', 'smartpay')}</th>
                            <th>{__('Type', 'smartpay')}</th>
                            <th>{__('Gifts', 'smartpay')}</th>
                            <th>{__('Total', 'smartpay')}</th>
                            <th>{__('Latest', 'smartpay')}</th>
                            <th>{__('Comment', 'smartpay')}</th>
                            <th>{__('Anon.', 'smartpay')}</th>
                            {extraColumns.map((c) => <th key={c.key}>{c.label}</th>)}
                        </tr>
                    </thead>
                    <tbody>
                        {!result ? (
                            <tr><td colSpan={colSpan} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
                        ) : rows.length === 0 ? (
                            <tr><td colSpan={colSpan}>
                                <div className="sp-empty">
                                    <div className="sp-empty__title">{__('No donors found', 'smartpay')}</div>
                                    <div className="sp-empty__desc">{__('Donors appear here after their first completed gift to this campaign.', 'smartpay')}</div>
                                </div>
                            </td></tr>
                        ) : rows.map((d) => (
                            <tr key={d.id}>
                                <td>
                                    <div className="sp-customer">
                                        <div className="sp-avatar" data-color={colorIndex(d.name)}>{initials(d.name)}</div>
                                        <div className="sp-customer__info">
                                            <Link to={`/donors/${d.id}`} className="sp-customer__name" style={{ textDecoration: 'none', color: 'inherit' }}>
                                                {d.name || d.email}
                                            </Link>
                                            <div className="sp-customer__email">{d.email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><DonorTypeBadge type={d.type} monthlyAmount={d.monthly_amount} /></td>
                                <td className="sp-cell--num" style={{ textAlign: 'left' }}>{count(d.gifts)}</td>
                                <td className="sp-cell--num" style={{ textAlign: 'left' }}>{money(d.total, 2)}</td>
                                <td className="sp-cell--muted sp-col--nowrap">{shortDate(d.latest)}</td>
                                <td className="sp-campaign-comment">{d.comment ? `“${d.comment}”` : <span className="sp-cell--muted">—</span>}</td>
                                <td>{d.anonymous ? <span className="sp-badge sp-badge--neutral">{__('Anonymous', 'smartpay')}</span> : <span className="sp-cell--muted">—</span>}</td>
                                {extraColumns.map((c) => <td key={c.key}>{c.render(d, () => load(result.current_page))}</td>)}
                            </tr>
                        ))}
                    </tbody>
                </table>

                {result?.total > 0 && (
                    <div className="sp-pagination">
                        <span className="sp-pagination__info">
                            {sprintf(__('Showing %1$d–%2$d of %3$d donors · gifts and totals count this campaign only', 'smartpay'), result.from, result.to, result.total)}
                        </span>
                        <div className="sp-pagination__nav">
                            <button className="sp-pagination__btn" disabled={result.current_page <= 1} onClick={() => load(result.current_page - 1)}>‹</button>
                            <span style={{ padding: '0 10px', fontSize: 12, color: 'var(--sp-text-muted)' }}>{result.current_page} / {result.last_page}</span>
                            <button className="sp-pagination__btn" disabled={result.current_page >= result.last_page} onClick={() => load(result.current_page + 1)}>›</button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    )
}
