import { CoverField, GoalFields } from './CampaignFields'

const { __ } = wp.i18n

export const SettingsTab = ({ campaign, draft, onChange }) => {
    const { Input, Label, Textarea } = window.WPSmartPayUI

    // Pro adds cards (e.g. Donor list) here: [{ key, element }]. Free shows none —
    // no locked teasers.
    const extraCards = window.wp?.hooks?.applyFilters?.('smartpay_campaign_settings_cards', [], { campaign, draft, onChange }) || []
    const base = (campaign.url || '').replace(/[^/]+\/?$/, '')

    return (
        <div className="sp-campaign-settings">
            <div className="sp-detail-card">
                <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Campaign details', 'smartpay')}</span></div>
                <div className="sp-detail-card__body sp-campaign-form">
                    <div className="sp-campaign-field">
                        <Label htmlFor="sp-campaign-title">{__('Title', 'smartpay')}</Label>
                        <Input id="sp-campaign-title" value={draft.title} onChange={(e) => onChange({ title: e.target.value })} />
                    </div>
                    <div className="sp-campaign-field">
                        <Label htmlFor="sp-campaign-slug">{__('Page URL', 'smartpay')}</Label>
                        <div className="sp-campaign-prefix">
                            <span>{base.replace(/^https?:\/\/[^/]+/, '') || '/campaign/'}</span>
                            <Input id="sp-campaign-slug" value={draft.slug} onChange={(e) => onChange({ slug: e.target.value })} />
                        </div>
                    </div>
                    <div className="sp-campaign-field">
                        <Label htmlFor="sp-campaign-desc">{__('Short description', 'smartpay')}</Label>
                        <Textarea id="sp-campaign-desc" rows={2} value={draft.description} onChange={(e) => onChange({ description: e.target.value })} />
                    </div>
                    <div className="sp-campaign-field">
                        <Label htmlFor="sp-campaign-story">{__('Story', 'smartpay')}</Label>
                        <Textarea id="sp-campaign-story" rows={6} value={draft.story} onChange={(e) => onChange({ story: e.target.value })} />
                        <span className="sp-cell--muted">{__('Shown under the progress bar on the campaign page.', 'smartpay')}</span>
                    </div>
                    <div className="sp-campaign-field">
                        <span className="sp-campaign-field__label">{__('Cover image', 'smartpay')}</span>
                        <CoverField coverUrl={draft.cover_url} onChange={onChange} />
                    </div>
                </div>
            </div>

            <div className="sp-detail-card">
                <div className="sp-detail-card__header"><span className="sp-detail-card__title">{__('Goal', 'smartpay')}</span></div>
                <div className="sp-detail-card__body sp-campaign-form">
                    <GoalFields value={draft} onChange={onChange} showBehavior />
                    <span className="sp-cell--muted">
                        {__('While a form is in this campaign, its own goal is paused and this goal is used instead.', 'smartpay')}
                    </span>
                </div>
            </div>

            {extraCards.map((c) => <div key={c.key}>{c.element}</div>)}
        </div>
    )
}
