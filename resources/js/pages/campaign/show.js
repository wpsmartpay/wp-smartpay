import { Link, useNavigate, useParams } from 'react-router-dom'
import { ExternalLink } from 'lucide-react'
import { GetCampaign, UpdateCampaign } from '../../http/campaign'
import { OverviewTab } from './OverviewTab'
import { FormsTab } from './FormsTab'
import { DonorsTab } from './DonorsTab'
import { SettingsTab } from './SettingsTab'
import { errorMessage } from './utils'

const { __ } = wp.i18n
const { useState, useEffect, useCallback } = wp.element

const TABS = [
    { id: 'overview', label: __('Overview', 'smartpay') },
    { id: 'forms', label: __('Forms', 'smartpay') },
    { id: 'donors', label: __('Donors', 'smartpay') },
    { id: 'settings', label: __('Settings', 'smartpay') },
]

// Fields the Settings tab edits; anything else in the draft (e.g. Pro's
// donor-list options) is sent too and saved by the add-on.
const toDraft = (c) => ({
    title: c.title,
    slug: c.slug,
    description: c.description,
    story: c.story,
    cover_id: c.cover_id,
    cover_url: c.cover_url,
    goal_type: c.goal_type,
    goal_target: c.goal_target,
    end_date: c.end_date,
    goal_behavior: c.goal_behavior,
    ...(window.wp?.hooks?.applyFilters?.('smartpay_campaign_settings_draft', {}, c) || {}),
})

export const ShowCampaign = () => {
    const { Header, Button } = window.WPSmartPayUI
    const { campaignId, tab = 'overview' } = useParams()
    const navigate = useNavigate()

    const [campaign, setCampaign] = useState(null)
    const [draft, setDraft] = useState(null)
    const [dirty, setDirty] = useState(false)
    const [saving, setSaving] = useState(false)
    const [notice, setNotice] = useState(null)
    const [missing, setMissing] = useState(false)

    const load = useCallback(async () => {
        try {
            const c = await GetCampaign(campaignId)
            setCampaign(c)
            setDraft(toDraft(c))
            setDirty(false)
        } catch (e) {
            setMissing(true)
        }
    }, [campaignId])

    useEffect(() => { load() }, [load])

    const changeDraft = (patch) => {
        setDraft((d) => ({ ...d, ...patch }))
        setDirty(true)
    }

    const save = async () => {
        setSaving(true)
        setNotice(null)
        try {
            const { cover_url, ...data } = draft
            const res = await UpdateCampaign(campaign.id, data)
            setCampaign(res.campaign)
            setDraft(toDraft(res.campaign))
            setDirty(false)
            setNotice({ type: 'success', text: __('Campaign saved.', 'smartpay') })
        } catch (e) {
            setNotice({ type: 'error', text: errorMessage(e) })
        } finally {
            setSaving(false)
        }
    }

    if (missing) {
        return (
            <div className="sp-layout">
                <Link to="/campaigns" className="sp-back-btn"><span className="sp-back-btn__arrow">←</span>{__('All Campaigns', 'smartpay')}</Link>
                <div className="sp-empty"><div className="sp-empty__title">{__('Campaign not found', 'smartpay')}</div></div>
            </div>
        )
    }

    // The SPA mounts with legacy render(), so these two state updates are not
    // batched: guard on both or the Settings tab renders with a null draft.
    if (!campaign || !draft) {
        return <div className="sp-layout"><div className="sp-state-loading">{__('Loading…', 'smartpay')}</div></div>
    }

    const activeTab = TABS.some((t) => t.id === tab) ? tab : 'overview'

    return (
        <>
            <Header title={campaign.title} subtitle={__('Campaign', 'smartpay')} />
            <div className="sp-layout">
                <Link to="/campaigns" className="sp-back-btn"><span className="sp-back-btn__arrow">←</span>{__('All Campaigns', 'smartpay')}</Link>

                <div className="sp-page-title__inner sp-campaign-head">
                    <div>
                        <h1 className="sp-page-title__heading">{campaign.title}</h1>
                        <p className="sp-page-title__sub">
                            <span className={`sp-badge sp-badge--dot ${campaign.status === 'active' ? 'sp-badge--active' : 'sp-badge--expired'}`}>
                                {campaign.status === 'active' ? __('Active', 'smartpay') : __('Ended', 'smartpay')}
                            </span>
                            <span className="sp-cell--muted">#{campaign.id}</span>
                        </p>
                    </div>
                    <div className="sp-campaign-head__actions">
                        {campaign.url && (
                            <a className="sp-btn sp-btn--outline" href={campaign.url} target="_blank" rel="noopener noreferrer">
                                {__('View page', 'smartpay')} <ExternalLink size={13} />
                            </a>
                        )}
                        <Button onClick={save} disabled={!dirty || saving}>
                            {saving ? __('Saving…', 'smartpay') : __('Save changes', 'smartpay')}
                        </Button>
                    </div>
                </div>

                {notice && (
                    <p className={`sp-campaign-notice sp-campaign-notice--${notice.type}`} role={notice.type === 'error' ? 'alert' : 'status'}>
                        {notice.text}
                    </p>
                )}

                <div className="sp-tabs__nav" role="tablist">
                    {TABS.map((t) => (
                        <button key={t.id} type="button" role="tab" aria-selected={activeTab === t.id}
                            className={activeTab === t.id ? 'sp-tabs__item sp-tabs__item--active' : 'sp-tabs__item'}
                            onClick={() => navigate(`/campaigns/${campaign.id}${t.id === 'overview' ? '' : '/' + t.id}`)}>
                            {t.label}
                        </button>
                    ))}
                </div>

                <div className="sp-campaign-tab">
                    {activeTab === 'overview' && <OverviewTab campaign={campaign} />}
                    {activeTab === 'forms' && <FormsTab campaign={campaign} onChanged={load} />}
                    {activeTab === 'donors' && <DonorsTab campaign={campaign} />}
                    {activeTab === 'settings' && <SettingsTab campaign={campaign} draft={draft} onChange={changeDraft} />}
                </div>
            </div>
        </>
    )
}
