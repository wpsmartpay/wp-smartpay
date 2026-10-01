import { Search } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { GetCampaigns, CreateCampaign, DeleteCampaign } from '../../http/campaign'
import { CoverField, GoalFields } from './CampaignFields'
import { money, count, goalValue, GoalBar, colorIndex, initials, errorMessage } from './utils'

const { __, sprintf } = wp.i18n
const { useState, useEffect, useCallback, useRef } = wp.element

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

/* ── New campaign modal ─────────────────────────────────── */

const EMPTY = { title: '', description: '', cover_id: 0, cover_url: '', goal_type: 'amount', goal_target: '', end_date: '' }

const NewCampaignModal = ({ open, onClose, onCreated }) => {
    const { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, Button, Input, Label, Textarea } = window.WPSmartPayUI
    const [value, setValue] = useState(EMPTY)
    const [saving, setSaving] = useState(false)
    const [error, setError] = useState('')
    // The WP media library opens outside the dialog (on <body>). While it is
    // open the dialog drops its focus trap and pointer blocking (modal=false)
    // and ignores outside clicks/Escape, so picking a cover never closes it.
    const [picking, setPicking] = useState(false)
    const keepOpen = (e) => picking && e.preventDefault()
    // Switching `modal` remounts the content; only auto-focus on a real open,
    // never on those remounts (it would steal focus from the library).
    const pickedOnce = useRef(false)
    const startPicking = (on) => {
        if (on) pickedOnce.current = true
        setPicking(on)
    }

    useEffect(() => {
        if (open) {
            setValue(EMPTY)
            setError('')
            setPicking(false)
            pickedOnce.current = false
        }
    }, [open])

    const change = (patch) => setValue((v) => ({ ...v, ...patch }))

    const submit = async (e) => {
        e.preventDefault()
        if (!value.title.trim()) {
            setError(__('Give the campaign a title.', 'smartpay'))
            return
        }
        setSaving(true)
        setError('')
        try {
            const { cover_url, ...data } = value
            const res = await CreateCampaign(data)
            onCreated(res.campaign)
        } catch (err) {
            setError(errorMessage(err))
        } finally {
            setSaving(false)
        }
    }

    return (
        <Dialog open={open} modal={!picking} onOpenChange={(o) => !o && !picking && onClose()}>
            <DialogContent className="sm:max-w-xl" onInteractOutside={keepOpen} onEscapeKeyDown={keepOpen}
                onOpenAutoFocus={(e) => pickedOnce.current && e.preventDefault()}>
                <form onSubmit={submit} className="sp-campaign-form">
                    <DialogHeader className="sp-campaign-form__header">
                        <DialogTitle>{__('New campaign', 'smartpay')}</DialogTitle>
                        <DialogDescription>{__('Name it and set one goal. You attach forms afterwards on the Forms tab.', 'smartpay')}</DialogDescription>
                    </DialogHeader>

                    <div className="sp-campaign-field">
                        <Label htmlFor="sp-new-campaign-title">{__('Title', 'smartpay')}</Label>
                        <Input id="sp-new-campaign-title" value={value.title} onChange={(e) => change({ title: e.target.value })} />
                    </div>

                    <div className="sp-campaign-field">
                        <Label htmlFor="sp-new-campaign-desc">{__('Short description', 'smartpay')}</Label>
                        <Textarea id="sp-new-campaign-desc" rows={2} value={value.description} onChange={(e) => change({ description: e.target.value })} />
                    </div>

                    <div className="sp-campaign-field">
                        <span className="sp-campaign-field__label">{__('Cover image', 'smartpay')}</span>
                        <CoverField coverUrl={value.cover_url} onChange={change} onPicking={startPicking} />
                    </div>

                    <GoalFields value={value} onChange={change} />

                    {error && <p className="sp-campaign-error" role="alert">{error}</p>}

                    <DialogFooter className="sp-campaign-form__footer">
                        <Button type="button" variant="outline" onClick={onClose}>{__('Cancel', 'smartpay')}</Button>
                        <Button type="submit" disabled={saving}>{saving ? __('Creating…', 'smartpay') : __('Create campaign', 'smartpay')}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    )
}

/* ── Row ────────────────────────────────────────────────── */

const CampaignRow = ({ campaign, openId, setOpenId, onDelete, extraCells }) => {
    const navigate = useNavigate()
    const isOpen = openId === campaign.id
    const p = campaign.progress || {}
    const hasGoal = p.target > 0

    return (
        <tr>
            <td>
                <div className="sp-customer">
                    <div className="sp-avatar" data-color={colorIndex(campaign.title)}>{initials(campaign.title)}</div>
                    <div className="sp-customer__info">
                        <a href={`#/campaigns/${campaign.id}`} className="sp-customer__name" style={{ textDecoration: 'none', color: 'inherit' }}>
                            {campaign.title}
                        </a>
                        <div className="sp-customer__email">#{campaign.id}</div>
                    </div>
                </div>
            </td>
            <td className="sp-cell--num" style={{ textAlign: 'left' }}>{count(campaign.forms_count)}</td>
            <td className="sp-cell--num" style={{ textAlign: 'left' }}>{money(p.raised)}</td>
            <td>
                {hasGoal ? (
                    <div className="sp-campaign-goal-cell">
                        <GoalBar progress={p} />
                        <span>{goalValue(p.current, p.type)} / {goalValue(p.target, p.type)}</span>
                    </div>
                ) : (
                    <span className="sp-cell--muted">—</span>
                )}
            </td>
            <td className="sp-cell--num" style={{ textAlign: 'left' }}>{count(p.donors)}</td>
            {extraCells}
            <td>
                <span className={`sp-badge sp-badge--dot ${campaign.status === 'active' ? 'sp-badge--active' : 'sp-badge--expired'}`}>
                    {campaign.status === 'active' ? __('Active', 'smartpay') : __('Ended', 'smartpay')}
                </span>
            </td>
            <td className="sp-cell--actions">
                <div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`} onClick={(e) => e.stopPropagation()}>
                    <button className="sp-row-actions__trigger" aria-label={__('Actions', 'smartpay')} onClick={() => setOpenId(isOpen ? null : campaign.id)}>
                        ···
                    </button>
                    <div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
                        <button className="sp-dropdown__item" onClick={() => navigate(`/campaigns/${campaign.id}`)}>{__('Open', 'smartpay')}</button>
                        {campaign.url && (
                            <a className="sp-dropdown__item" href={campaign.url} target="_blank" rel="noopener noreferrer">
                                {__('View page', 'smartpay')}
                            </a>
                        )}
                        <div className="sp-dropdown__divider" />
                        <button className="sp-dropdown__item sp-dropdown__item--destructive" onClick={() => { setOpenId(null); onDelete(campaign) }}>
                            {__('Delete', 'smartpay')}
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    )
}

/* ── List ───────────────────────────────────────────────── */

export const CampaignList = () => {
    const { Header } = window.WPSmartPayUI
    const navigate = useNavigate()

    const [rows, setRows] = useState([])
    const [loading, setLoading] = useState(false)
    const [search, setSearch] = useState('')
    const [debounced, setDebounced] = useState('')
    const [status, setStatus] = useState('')
    const [perPage, setPerPage] = useState(20)
    const [openId, setOpenId] = useState(null)
    const [showNew, setShowNew] = useState(false)
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

    // Pro adds extra columns (e.g. Donor list) through this filter: [{ key, label, render(campaign) }].
    const extraColumns = window.wp?.hooks?.applyFilters?.('smartpay_campaign_list_columns', []) || []

    useEffect(() => {
        const t = setTimeout(() => setDebounced(search), 400)
        return () => clearTimeout(t)
    }, [search])

    const load = useCallback(async (page = 1) => {
        setLoading(true)
        try {
            const { data = [], ...pg } = await GetCampaigns({ page, per_page: perPage, search: debounced, status })
            setRows(data)
            setPagination(pg)
        } catch (e) {
            console.error('Failed to load campaigns', e)
        } finally {
            setLoading(false)
        }
    }, [perPage, debounced, status])

    useEffect(() => { load(1) }, [load])

    useEffect(() => {
        const close = () => setOpenId(null)
        document.addEventListener('click', close)
        return () => document.removeEventListener('click', close)
    }, [])

    const remove = async (campaign) => {
        if (!window.confirm(sprintf(__('Delete “%s”? Its forms are kept and become unassigned.', 'smartpay'), campaign.title))) return
        try {
            await DeleteCampaign(campaign.id)
            load(pagination.current_page)
        } catch (e) {
            window.alert(errorMessage(e))
        }
    }

    const colSpan = 7 + extraColumns.length

    return (
        <>
            <Header title={__('Campaigns', 'smartpay')} subtitle={__('Group forms under one goal', 'smartpay')} />

            <div className="sp-layout">
                <div className="sp-page-title__inner">
                    <h1 className="sp-page-title__heading">{__('Campaigns', 'smartpay')}</h1>
                    <p className="sp-page-title__sub">{__('Group forms under one goal', 'smartpay')}</p>
                </div>

                <div className="sp-toolbar">
                    <div className="sp-search">
                        <Search className="sp-search__icon" size={14} />
                        <input type="search" className="sp-search__input" placeholder={__('Search campaigns', 'smartpay')}
                            value={search} onChange={(e) => setSearch(e.target.value)} />
                    </div>
                    <select className="sp-filter-select" value={status} onChange={(e) => setStatus(e.target.value)} aria-label={__('Status', 'smartpay')}>
                        <option value="">{__('All statuses', 'smartpay')}</option>
                        <option value="active">{__('Active', 'smartpay')}</option>
                        <option value="ended">{__('Ended', 'smartpay')}</option>
                    </select>
                    <div className="sp-toolbar__spacer" />
                    <button className="sp-btn sp-btn--primary" onClick={(e) => { e.stopPropagation(); setShowNew(true) }}>
                        + {__('New Campaign', 'smartpay')}
                    </button>
                </div>

                <div className="sp-table-card">
                    <table className="sp-table">
                        <thead>
                            <tr>
                                <th>{__('Campaign', 'smartpay')}</th>
                                <th>{__('Forms', 'smartpay')}</th>
                                <th>{__('Raised', 'smartpay')}</th>
                                <th>{__('Goal', 'smartpay')}</th>
                                <th>{__('Donors', 'smartpay')}</th>
                                {extraColumns.map((c) => <th key={c.key}>{c.label}</th>)}
                                <th>{__('Status', 'smartpay')}</th>
                                <th className="sp-col--actions"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan={colSpan} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
                            ) : rows.length === 0 ? (
                                <tr><td colSpan={colSpan}>
                                    <div className="sp-empty">
                                        <div className="sp-empty__icon">🎯</div>
                                        <div className="sp-empty__title">{__('No campaigns yet', 'smartpay')}</div>
                                        <div className="sp-empty__desc">
                                            {search || status
                                                ? __('No campaigns match these filters.', 'smartpay')
                                                : __('A campaign groups your donation forms so every gift moves one goal.', 'smartpay')}
                                        </div>
                                        {!search && !status && (
                                            <button className="sp-btn sp-btn--primary" onClick={(e) => { e.stopPropagation(); setShowNew(true) }}>
                                                + {__('New Campaign', 'smartpay')}
                                            </button>
                                        )}
                                    </div>
                                </td></tr>
                            ) : rows.map((c) => (
                                <CampaignRow key={c.id} campaign={c} openId={openId} setOpenId={setOpenId} onDelete={remove}
                                    extraCells={extraColumns.map((col) => <td key={col.key}>{col.render(c)}</td>)} />
                            ))}
                        </tbody>
                    </table>

                    {pagination.total > 0 && (
                        <div className="sp-pagination">
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                <span className="sp-pagination__info">
                                    {sprintf(__('Showing %1$d–%2$d of %3$d campaigns', 'smartpay'), pagination.from, pagination.to, pagination.total)}
                                </span>
                                <select className="sp-filter-select" style={{ fontSize: 12, padding: '0 22px 0 8px' }}
                                    value={perPage} onChange={(e) => setPerPage(Number(e.target.value))}>
                                    {PER_PAGE_OPTIONS.map((n) => <option key={n} value={n}>{sprintf(__('%d per page', 'smartpay'), n)}</option>)}
                                </select>
                            </div>
                            <div className="sp-pagination__nav">
                                <button className="sp-pagination__btn" disabled={pagination.current_page <= 1} onClick={() => load(pagination.current_page - 1)}>‹</button>
                                <span style={{ padding: '0 10px', fontSize: 12, color: 'var(--sp-text-muted)' }}>{pagination.current_page} / {pagination.last_page}</span>
                                <button className="sp-pagination__btn" disabled={pagination.current_page >= pagination.last_page} onClick={() => load(pagination.current_page + 1)}>›</button>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <NewCampaignModal open={showNew} onClose={() => setShowNew(false)}
                onCreated={(c) => { setShowNew(false); navigate(`/campaigns/${c.id}/forms`) }} />
        </>
    )
}
