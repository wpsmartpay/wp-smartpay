import { GOAL_TYPES, currencySymbol } from './utils'

const { __ } = wp.i18n

/** Cover picker backed by the WP media library. */
export const CoverField = ({ coverUrl, onChange }) => {
    const { Button } = window.WPSmartPayUI

    const pick = () => {
        const frame = wp.media({ multiple: false, library: { type: 'image' } })
        frame.on('select', () => {
            const file = frame.state().get('selection').first().toJSON()
            onChange({ cover_id: file.id, cover_url: file.url })
        })
        frame.open()
    }

    return (
        <div className="sp-campaign-cover">
            {coverUrl ? (
                <>
                    <img src={coverUrl} alt="" />
                    <div className="sp-campaign-cover__actions">
                        <Button type="button" variant="outline" size="sm" onClick={pick}>{__('Replace', 'smartpay')}</Button>
                        <Button type="button" variant="ghost" size="sm" onClick={() => onChange({ cover_id: 0, cover_url: '' })}>
                            {__('Remove', 'smartpay')}
                        </Button>
                    </div>
                </>
            ) : (
                <button type="button" className="sp-campaign-cover__drop" onClick={pick}>
                    {__('Choose an image · 1600×600 recommended', 'smartpay')}
                </button>
            )}
        </div>
    )
}

/** Goal type (three cards), target and optional end date. */
export const GoalFields = ({ value, onChange, showBehavior = false }) => {
    const { Input, Label } = window.WPSmartPayUI

    return (
        <>
            <div className="sp-campaign-field">
                <span className="sp-campaign-field__label">{__('Goal type', 'smartpay')}</span>
                <div className="sp-campaign-choices" role="radiogroup" aria-label={__('Goal type', 'smartpay')}>
                    {GOAL_TYPES.map((t) => (
                        <label key={t.value} className={`sp-campaign-choice${value.goal_type === t.value ? ' is-active' : ''}`}>
                            <input
                                type="radio"
                                name="sp-campaign-goal-type"
                                value={t.value}
                                checked={value.goal_type === t.value}
                                onChange={() => onChange({ goal_type: t.value })}
                            />
                            <span>
                                <b>{t.label}</b>
                                <small>{t.help}</small>
                            </span>
                        </label>
                    ))}
                </div>
            </div>

            <div className="sp-field--row">
                <div className="sp-campaign-field">
                    <Label htmlFor="sp-campaign-target">{__('Goal target', 'smartpay')}</Label>
                    <div className="sp-campaign-prefix">
                        {value.goal_type === 'amount' && <span>{currencySymbol()}</span>}
                        <Input
                            id="sp-campaign-target"
                            type="number"
                            min="0"
                            step="any"
                            value={value.goal_target ?? ''}
                            onChange={(e) => onChange({ goal_target: e.target.value })}
                        />
                    </div>
                </div>
                <div className="sp-campaign-field">
                    <Label htmlFor="sp-campaign-end">{__('End date (optional)', 'smartpay')}</Label>
                    <Input
                        id="sp-campaign-end"
                        type="date"
                        value={value.end_date || ''}
                        onChange={(e) => onChange({ end_date: e.target.value })}
                    />
                </div>
            </div>

            {showBehavior && (
                <div className="sp-campaign-field">
                    <span className="sp-campaign-field__label">{__('When goal is reached', 'smartpay')}</span>
                    <div className="sp-period-selector" role="radiogroup">
                        {[
                            ['keep', __('Keep accepting', 'smartpay')],
                            ['stop', __('Stop', 'smartpay')],
                        ].map(([key, label]) => (
                            <button
                                key={key}
                                type="button"
                                role="radio"
                                aria-checked={value.goal_behavior === key}
                                className={`sp-period-selector__item${value.goal_behavior === key ? ' sp-period-selector__item--active' : ''}`}
                                onClick={() => onChange({ goal_behavior: key })}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </>
    )
}
