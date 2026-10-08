import {
    GetCampaignForms,
    AttachCampaignForms,
    DetachCampaignForm,
    SetCampaignDefaultForm,
    GetUnassignedForms,
    MigrateLegacyForm,
} from '../../http/campaign'
import { money, count, colorIndex, initials, errorMessage } from './utils'

const { __, sprintf, _n } = wp.i18n
const { useState, useEffect, useCallback } = wp.element

/* ── Attach existing modal ──────────────────────────────── */

const AttachModal = ({ open, campaign, legacy, onClose, onDone }) => {
    const { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, Button } = window.WPSmartPayUI
    const [forms, setForms] = useState(null)
    const [picked, setPicked] = useState(new Set())
    const [busy, setBusy] = useState(false)
    const [migrating, setMigrating] = useState(0)
    const [error, setError] = useState('')

    const load = useCallback(() => {
        setForms(null)
        GetUnassignedForms().then(setForms).catch(() => setForms([]))
    }, [])

    useEffect(() => {
        if (open) {
            setPicked(new Set())
            setError('')
            load()
        }
    }, [open, load])

    const toggle = (id) => {
        const next = new Set(picked)
        next.has(id) ? next.delete(id) : next.add(id)
        setPicked(next)
    }

    const migrate = async (legacyId) => {
        setMigrating(legacyId)
        setError('')
        try {
            const res = await MigrateLegacyForm(legacyId)
            const result = res?.results?.[0]
            if (result?.error) throw new Error(result.error)
            if (result?.post_id) await AttachCampaignForms(campaign.id, [result.post_id])
            onDone()
            load()
        } catch (e) {
            setError(errorMessage(e))
        } finally {
            setMigrating(0)
        }
    }

    const attach = async () => {
        setBusy(true)
        setError('')
        try {
            await AttachCampaignForms(campaign.id, [...picked])
            onDone()
            onClose()
        } catch (e) {
            setError(errorMessage(e))
        } finally {
            setBusy(false)
        }
    }

    return (
        <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{__('Attach existing forms', 'smartpay')}</DialogTitle>
                    <DialogDescription>{__('Forms not in any campaign. A form belongs to one campaign or none.', 'smartpay')}</DialogDescription>
                </DialogHeader>

                <div className="sp-campaign-picklist">
                    {forms === null ? (
                        <div className="sp-state-loading">{__('Loading…', 'smartpay')}</div>
                    ) : forms.length === 0 ? (
                        <div className="sp-cell--muted">{__('Every form is already in a campaign.', 'smartpay')}</div>
                    ) : forms.map((f) => (
                        <label key={f.id} className="sp-campaign-picklist__row">
                            <input type="checkbox" className="sp-checkbox" checked={picked.has(f.id)} onChange={() => toggle(f.id)} />
                            <span>{f.title}</span>
                            <span className="sp-cell--muted">#{f.id}</span>
                        </label>
                    ))}
                </div>

                {legacy.length > 0 && (
                    <div className="sp-campaign-picklist">
                        <p className="sp-campaign-field__label">{__('Legacy forms', 'smartpay')}</p>
                        <p className="sp-cell--muted" style={{ margin: 0 }}>
                            {__('Only forms made with the new form builder can join. Migrate a legacy form to add it here.', 'smartpay')}
                        </p>
                        {legacy.map((f) => (
                            <div key={f.id} className="sp-campaign-picklist__row">
                                <span>{f.title}</span>
                                <span className="sp-cell--muted">#{f.id}</span>
                                <Button variant="outline" size="sm" disabled={!!migrating} onClick={() => migrate(f.id)}>
                                    {migrating === f.id ? __('Migrating…', 'smartpay') : __('Migrate to join a campaign', 'smartpay')}
                                </Button>
                            </div>
                        ))}
                    </div>
                )}

                {error && <p className="sp-campaign-error" role="alert">{error}</p>}

                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>{__('Cancel', 'smartpay')}</Button>
                    <Button disabled={!picked.size || busy} onClick={attach}>
                        {busy ? __('Attaching…', 'smartpay') : sprintf(_n('Attach %d form', 'Attach %d forms', picked.size || 0, 'smartpay'), picked.size)}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

/* ── Tab ─────────────────────────────────────────────────── */

export const FormsTab = ({ campaign, onChanged }) => {
    const { Button } = window.WPSmartPayUI
    const [forms, setForms] = useState(null)
    const [legacy, setLegacy] = useState([])
    const [openId, setOpenId] = useState(null)
    const [attachOpen, setAttachOpen] = useState(false)
    const [error, setError] = useState('')

    const load = useCallback(async () => {
        try {
            const res = await GetCampaignForms(campaign.id)
            setForms(res.forms || [])
            setLegacy(res.legacy_forms || [])
        } catch (e) {
            setForms([])
            setError(errorMessage(e))
        }
    }, [campaign.id])

    useEffect(() => { load() }, [load])

    useEffect(() => {
        const close = () => setOpenId(null)
        document.addEventListener('click', close)
        return () => document.removeEventListener('click', close)
    }, [])

    const refresh = () => { load(); onChanged() }

    const run = async (fn) => {
        setError('')
        try {
            await fn()
            refresh()
        } catch (e) {
            setError(errorMessage(e))
        }
    }

    const detach = (form) => {
        if (!window.confirm(sprintf(__('Detach “%s”? The form is kept and goes back to its own goal.', 'smartpay'), form.title))) return
        run(() => DetachCampaignForm(campaign.id, form.id))
    }

    // The form editor reads sp_campaign and pre-selects this campaign on a new form.
    const addUrl = `${window.smartpay.adminUrl.replace(/admin\.php$/, 'post-new.php')}?post_type=smartpay_form&sp_campaign=${campaign.id}`

    return (
        <div className="sp-stack">
            <div className="sp-toolbar" style={{ marginBottom: 0 }}>
                <span className="sp-cell--muted">
                    {sprintf(_n('%d form', '%d forms', forms?.length || 0, 'smartpay'), forms?.length || 0)} · {__('the ★ default form powers “Donate now” on the campaign page', 'smartpay')}
                </span>
                <div className="sp-toolbar__spacer" />
                <Button variant="outline" onClick={(e) => { e.stopPropagation(); setAttachOpen(true) }}>{__('Attach existing', 'smartpay')}</Button>
                <a className="sp-btn sp-btn--primary" href={addUrl}>+ {__('Add form', 'smartpay')}</a>
            </div>

            {error && <p className="sp-campaign-error" role="alert">{error}</p>}

            <div className="sp-table-card">
                <table className="sp-table">
                    <thead>
                        <tr>
                            <th>{__('Form', 'smartpay')}</th>
                            <th>{__('Default', 'smartpay')}</th>
                            <th>{__('Raised', 'smartpay')}</th>
                            <th>{__('Donations', 'smartpay')}</th>
                            <th>{__('Shortcode', 'smartpay')}</th>
                            <th>{__('Status', 'smartpay')}</th>
                            <th className="sp-col--actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {forms === null ? (
                            <tr><td colSpan={7} className="sp-state-loading">{__('Loading…', 'smartpay')}</td></tr>
                        ) : forms.length === 0 ? (
                            <tr><td colSpan={7}>
                                <div className="sp-empty">
                                    <div className="sp-empty__title">{__('No forms in this campaign yet', 'smartpay')}</div>
                                    <div className="sp-empty__desc">{__('Add a new form or attach one you already have.', 'smartpay')}</div>
                                </div>
                            </td></tr>
                        ) : forms.map((f) => {
                            const isOpen = openId === f.id
                            return (
                                <tr key={f.id}>
                                    <td>
                                        <div className="sp-customer">
                                            <div className="sp-avatar" data-color={colorIndex(f.title)}>{initials(f.title)}</div>
                                            <div className="sp-customer__info">
                                                <a href={f.edit_url} className="sp-customer__name" style={{ textDecoration: 'none', color: 'inherit' }}>{f.title}</a>
                                                <div className="sp-customer__email">#{f.id}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" className={`sp-campaign-star${f.is_default ? ' is-default' : ''}`}
                                            aria-pressed={f.is_default}
                                            title={f.is_default ? __('Default form', 'smartpay') : __('Set as default', 'smartpay')}
                                            onClick={() => !f.is_default && run(() => SetCampaignDefaultForm(campaign.id, f.id))}>
                                            {f.is_default ? '★' : '☆'}
                                        </button>
                                    </td>
                                    <td className="sp-cell--num" style={{ textAlign: 'left' }}>{money(f.raised)}</td>
                                    <td className="sp-cell--num" style={{ textAlign: 'left' }}>{count(f.donations)}</td>
                                    <td><code className="sp-campaign-code">{f.shortcode}</code></td>
                                    <td>
                                        <span className={`sp-badge sp-badge--dot ${f.status === 'publish' ? 'sp-badge--active' : 'sp-badge--expired'}`}>
                                            {f.status === 'publish' ? __('Published', 'smartpay') : f.status}
                                        </span>
                                    </td>
                                    <td className="sp-cell--actions">
                                        <div className={`sp-row-actions${isOpen ? ' sp-row-actions--open' : ''}`} onClick={(e) => e.stopPropagation()}>
                                            <button className="sp-row-actions__trigger" aria-label={__('Actions', 'smartpay')} onClick={() => setOpenId(isOpen ? null : f.id)}>···</button>
                                            <div className={`sp-dropdown${isOpen ? ' sp-dropdown--open' : ''}`}>
                                                <a className="sp-dropdown__item" href={f.edit_url}>{__('Edit form', 'smartpay')}</a>
                                                {!f.is_default && (
                                                    <button className="sp-dropdown__item" onClick={() => { setOpenId(null); run(() => SetCampaignDefaultForm(campaign.id, f.id)) }}>
                                                        {__('Set as default', 'smartpay')}
                                                    </button>
                                                )}
                                                <div className="sp-dropdown__divider" />
                                                <button className="sp-dropdown__item sp-dropdown__item--destructive" onClick={() => { setOpenId(null); detach(f) }}>
                                                    {__('Detach', 'smartpay')}
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            )
                        })}
                    </tbody>
                </table>
            </div>

            <p className="sp-campaign-info">
                {__('Only forms made with the new form builder can join a campaign. Legacy forms can be migrated from “Attach existing”.', 'smartpay')}
            </p>

            <AttachModal open={attachOpen} campaign={campaign} legacy={legacy} onClose={() => setAttachOpen(false)} onDone={refresh} />
        </div>
    )
}
