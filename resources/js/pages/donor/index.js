import { ChevronDown, Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import { GetDonors, GetCampaigns } from '../../http/campaign'
import { money, count, colorIndex, initials, shortDate, DonorTypeBadge, DONOR_TYPES } from '../campaign/utils'

const { __, sprintf } = wp.i18n
const { useState, useEffect, useCallback } = wp.element

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

/** Contacts › Donors. */
export const DonorList = ({ tabs = null }) => {
    const { Header } = window.WPSmartPayUI

    const [search, setSearch] = useState('')
    const [debounced, setDebounced] = useState('')
    const [campaign, setCampaign] = useState('')
    const [type, setType] = useState('')
    const [orderby, setOrderby] = useState('latest')
    const [perPage, setPerPage] = useState(20)
    const [campaigns, setCampaigns] = useState([])
    const [result, setResult] = useState(null)
    const [checkedIds, setCheckedIds] = useState(new Set())
    const [actionOpen, setActionOpen] = useState(false)

    // Pro adds toolbar actions (e.g. Export CSV): (actions, filters) => [elements].
    const actions = window.wp?.hooks?.applyFilters?.('smartpay_donor_list_actions', [], { campaign, type, search: debounced }) || []

    useEffect(() => {
        GetCampaigns({ per_page: 100 }).then((r) => setCampaigns(r.data || [])).catch(() => setCampaigns([]))
    }, [])

    useEffect(() => {
        const t = setTimeout(() => setDebounced(search), 400)
        return () => clearTimeout(t)
    }, [search])

    const load = useCallback((page = 1) => {
        setResult(null)
        setCheckedIds(new Set())
        GetDonors({ page, per_page: perPage, search: debounced, campaign, type, orderby })
            .then(setResult)
            .catch(() => setResult({ data: [], total: 0, counts: {} }))
    }, [perPage, debounced, campaign, type, orderby])

    useEffect(() => { load(1) }, [load])

    useEffect(() => {
        const close = () => setActionOpen(false)
        document.addEventListener('click', close)
        return () => document.removeEventListener('click', close)
    }, [])

    const rows = result?.data || []

    const allChecked   = rows.length > 0 && checkedIds.size === rows.length
    const someChecked  = checkedIds.size > 0 && checkedIds.size < rows.length
    const hasSelection = checkedIds.size > 0

    const toggleAll = () => setCheckedIds(allChecked || someChecked ? new Set() : new Set(rows.map((d) => d.id)))
    const toggleRow = (id) => {
        const next = new Set(checkedIds)
        next.has(id) ? next.delete(id) : next.add(id)
        setCheckedIds(next)
    }

    // A donor is a customer record, so bulk delete reuses the customer endpoint.
    const bulkDelete = async () => {
        if (!window.confirm(__('Delete all selected donors? This cannot be undone.', 'smartpay'))) return
        const baseUrl = window.smartpay.restUrl.replace(/\/$/, '')
        await Promise.all([...checkedIds].map((id) => fetch(`${baseUrl}/v1/customers/${id}`, {
            method: 'DELETE',
            headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
        })))
        load(1)
    }

    const sortHeader = (key, label) => (
        <th>
            <button type="button" className="sp-campaign-sort" aria-pressed={orderby === key}
                onClick={() => setOrderby(orderby === key && key === 'latest' ? 'oldest' : key)}>
                {label}{(orderby === key || (key === 'latest' && orderby === 'oldest')) && (orderby === 'oldest' ? ' ▴' : ' ▾')}
            </button>
        </th>
    )

    return (
        <>
            <Header title={__('Contacts', 'smartpay')} subtitle={__('Everyone who paid or gave', 'smartpay')} />

            <div className="sp-layout">
                <div className="sp-page-title__inner">
                    <h1 className="sp-page-title__heading">{__('Contacts', 'smartpay')}</h1>
                    <p className="sp-page-title__sub">{__('Everyone who paid or gave', 'smartpay')}</p>
                </div>

                {tabs}

                <div className="sp-toolbar">
                    <div className="sp-search">
                        <Search className="sp-search__icon" size={14} />
                        <input type="search" className="sp-search__input" placeholder={__('Search by name or email', 'smartpay')}
                            value={search} onChange={(e) => setSearch(e.target.value)} />
                    </div>
                    <select className="sp-filter-select" value={campaign} onChange={(e) => setCampaign(e.target.value)} aria-label={__('Campaign', 'smartpay')}>
                        <option value="">{__('All campaigns', 'smartpay')}</option>
                        {campaigns.map((c) => <option key={c.id} value={c.id}>{c.title}</option>)}
                    </select>
                    <select className="sp-filter-select" value={type} onChange={(e) => setType(e.target.value)} aria-label={__('Donor type', 'smartpay')}>
                        <option value="">{__('All types', 'smartpay')}</option>
                        {Object.entries(DONOR_TYPES).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
                    </select>
                    {hasSelection && (
                        <span className="sp-selection-count">
                            {checkedIds.size} {__('selected', 'smartpay')}
                            <button className="sp-selection-count__clear"
                                onClick={() => setCheckedIds(new Set())} title={__('Clear selection', 'smartpay')}>
                                ✕
                            </button>
                        </span>
                    )}
                    <div className="sp-toolbar__spacer" />
                    {actions}
                    <div className="sp-action-dropdown" onClick={(e) => e.stopPropagation()}>
                        <button className="sp-btn sp-btn--outline" disabled={!hasSelection} onClick={() => setActionOpen((o) => !o)}>
                            {__('Select Action', 'smartpay')}
                            <ChevronDown size={14} style={{ marginLeft: 2, opacity: 0.6 }} />
                        </button>
                        <div className={`sp-dropdown${actionOpen ? ' sp-dropdown--open' : ''}`}>
                            <button className="sp-dropdown__item sp-dropdown__item--destructive"
                                onClick={() => { setActionOpen(false); bulkDelete() }}>
                                {__('Delete selected', 'smartpay')}
                            </button>
                        </div>
                    </div>
                </div>

                <div className="sp-table-card">
                    <table className="sp-table">
                        <thead>
                            <tr>
                                <th className="sp-col--check">
                                    <input type="checkbox" className="sp-checkbox sp-select-all" checked={allChecked}
                                        ref={(el) => { if (el) el.indeterminate = someChecked }} onChange={toggleAll} />
                                </th>
                                <th>{__('Donor', 'smartpay')}</th>
                                <th>{__('Type', 'smartpay')}</th>
                                {sortHeader('total', __('Total given', 'smartpay'))}
                                {sortHeader('gifts', __('Gifts', 'smartpay'))}
                                <th>{__('Campaigns', 'smartpay')}</th>
                                {sortHeader('latest', __('Latest gift', 'smartpay'))}
                            </tr>
                        </thead>
                        <tbody>
                            {!result ? (
                                <tr><td colSpan={7} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
                            ) : rows.length === 0 ? (
                                <tr><td colSpan={7}>
                                    <div className="sp-empty">
                                        <div className="sp-empty__icon">💚</div>
                                        <div className="sp-empty__title">{__('No donors yet', 'smartpay')}</div>
                                        <div className="sp-empty__desc">
                                            {__('A customer becomes a donor with their first completed gift on a campaign form or a form marked “donation form”.', 'smartpay')}
                                        </div>
                                    </div>
                                </td></tr>
                            ) : rows.map((d) => (
                                <tr key={d.id} className={checkedIds.has(d.id) ? 'sp-row--selected' : ''}>
                                    <td className="sp-col--check">
                                        <input type="checkbox" className="sp-checkbox sp-row-check"
                                            checked={checkedIds.has(d.id)} onChange={() => toggleRow(d.id)} />
                                    </td>
                                    <td>
                                        <div className="sp-customer">
                                            <div className="sp-avatar" data-color={colorIndex(d.name)}>{initials(d.name || d.email)}</div>
                                            <div className="sp-customer__info">
                                                <Link to={`/donors/${d.id}`} className="sp-customer__name" style={{ textDecoration: 'none', color: 'inherit' }}>
                                                    {d.name || d.email}
                                                </Link>
                                                <div className="sp-customer__email">{d.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <DonorTypeBadge type={d.type} monthlyAmount={d.monthly_amount} />
                                        {d.anonymous > 0 && (
                                            <span className="sp-badge sp-badge--neutral" style={{ marginLeft: 4 }}>
                                                {sprintf(__('Anonymous ×%d', 'smartpay'), d.anonymous)}
                                            </span>
                                        )}
                                    </td>
                                    <td className="sp-cell--num" style={{ textAlign: 'left' }}>{money(d.total, 2)}</td>
                                    <td className="sp-cell--num" style={{ textAlign: 'left' }}>{count(d.gifts)}</td>
                                    <td>
                                        {d.campaigns.slice(0, 2).map((c) => (
                                            <Link key={c.id} to={`/campaigns/${c.id}`} className="sp-badge sp-badge--neutral" style={{ marginRight: 4 }}>{c.title}</Link>
                                        ))}
                                        {d.campaigns.length > 2 && <span className="sp-cell--muted">+{d.campaigns.length - 2}</span>}
                                        {!d.campaigns.length && <span className="sp-cell--muted">{__('Donation form', 'smartpay')}</span>}
                                    </td>
                                    <td className="sp-cell--muted sp-col--nowrap">{shortDate(d.latest)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {result?.total > 0 && (
                        <div className="sp-pagination">
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                <span className="sp-pagination__info">
                                    {sprintf(__('Showing %1$d–%2$d of %3$d donors', 'smartpay'), result.from, result.to, result.total)}
                                </span>
                                <select className="sp-filter-select" style={{ fontSize: 12, padding: '0 22px 0 8px' }}
                                    value={perPage} onChange={(e) => setPerPage(Number(e.target.value))}>
                                    {PER_PAGE_OPTIONS.map((n) => <option key={n} value={n}>{sprintf(__('%d per page', 'smartpay'), n)}</option>)}
                                </select>
                            </div>
                            <div className="sp-pagination__nav">
                                <button className="sp-pagination__btn" disabled={result.current_page <= 1} onClick={() => load(result.current_page - 1)}>‹</button>
                                <span style={{ padding: '0 10px', fontSize: 12, color: 'var(--sp-text-muted)' }}>{result.current_page} / {result.last_page}</span>
                                <button className="sp-pagination__btn" disabled={result.current_page >= result.last_page} onClick={() => load(result.current_page + 1)}>›</button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    )
}
