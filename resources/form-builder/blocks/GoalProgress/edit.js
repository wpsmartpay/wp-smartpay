import {
    PanelBody,
    Button,
    Notice,
    RangeControl,
    TextControl,
    ToggleControl,
} from '@wordpress/components'
import {
    InspectorControls,
    PanelColorSettings,
    useBlockProps,
} from '@wordpress/block-editor'
import { useEntityProp } from '@wordpress/core-data'
import { useDispatch } from '@wordpress/data'
import { __ } from '@wordpress/i18n'

/**
 * Parse the (double-encoded) goal config out of _smartpay_settings, matching
 * the form-editor-sidebar GoalPanel shape exactly.
 */
const readGoal = (meta) => {
    let settings = {}
    try {
        const parsed = JSON.parse(meta?._smartpay_settings || '{}')
        settings = parsed && typeof parsed === 'object' ? parsed : {}
    } catch {
        settings = {}
    }
    let goal = {}
    try {
        const raw = settings.goal || '{}'
        const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw
        goal = parsed && typeof parsed === 'object' ? parsed : {}
    } catch {
        goal = {}
    }
    return goal
}

export const edit = ({ attributes, setAttributes }) => {
    const {
        showBar,
        showCounts,
        showPercentage,
        showMessage,
        messageTemplate,
        bgColor,
        barColor,
        trackColor,
        textColor,
        barHeight,
        barRadius,
        cardRadius,
        padding,
        fontSize,
        previewPercent,
    } = attributes

    const [meta] = useEntityProp('postType', 'smartpay_form', 'meta')
    const { openGeneralSidebar } = useDispatch('core/edit-post')

    const goal = readGoal(meta)
    const goalEnabled = !!goal.enabled
    const type = goal.type || 'quantity'
    const target = Number(goal.target ?? 100)
    const goalMetMessage = goal.goalMetMessage || __('Goal reached!', 'smartpay')
    const unit = 'quantity' === type ? __('sold', 'smartpay') : __('raised', 'smartpay')

    const current = Math.round((target * (previewPercent || 0)) / 100)
    const goalReached = current >= target && target > 0

    const blockProps = useBlockProps({ className: 'smartpay-goal-progress-block' })

    const cardStyle = {
        marginBottom: '20px',
        padding: `${padding}px`,
        background: bgColor || '#f8f9fa',
        borderRadius: `${cardRadius}px`,
        textAlign: 'left',
        color: textColor || '#555',
        fontSize: `${fontSize}px`,
    }

    const renderCountsText = () => {
        if (messageTemplate) {
            return messageTemplate
                .replace('{current}', current.toLocaleString())
                .replace('{target}', target.toLocaleString())
                .replace('{percent}', String(previewPercent))
                .replace('{unit}', unit)
        }
        return null
    }

    return (
        <>
            <div {...blockProps}>
                {!goalEnabled ? (
                    <Notice status="warning" isDismissible={false}>
                        {__('Goal is off. Enable it in Form Settings → Goal.', 'smartpay')}
                        <div style={{ marginTop: '8px' }}>
                            <Button
                                variant="secondary"
                                onClick={() => openGeneralSidebar?.('edit-post/document')}
                            >
                                {__('Open Form Settings', 'smartpay')}
                            </Button>
                        </div>
                    </Notice>
                ) : (
                    <div className="smartpay-goal-progress" style={cardStyle}>
                        {goalReached && showMessage ? (
                            <p style={{ margin: '0 0 12px', fontWeight: 600, color: barColor }}>
                                {goalMetMessage}
                            </p>
                        ) : (
                            showCounts && (
                                <p style={{ margin: '0 0 8px' }}>
                                    {renderCountsText() || (
                                        <>
                                            <strong>{current.toLocaleString()}</strong>
                                            {' / '}
                                            {target.toLocaleString()} {unit}
                                        </>
                                    )}
                                </p>
                            )
                        )}

                        {showBar && (
                            <div
                                style={{
                                    background: trackColor || '#e9ecef',
                                    borderRadius: `${barRadius}px`,
                                    height: `${barHeight}px`,
                                    overflow: 'hidden',
                                }}
                            >
                                <div
                                    style={{
                                        width: `${previewPercent}%`,
                                        background: barColor || '#28a745',
                                        height: '100%',
                                        borderRadius: `${barRadius}px`,
                                    }}
                                />
                            </div>
                        )}

                        {showPercentage && !goalReached && (
                            <p style={{ margin: '8px 0 0', fontSize: '0.85em', textAlign: 'right', opacity: 0.7 }}>
                                {previewPercent}%
                            </p>
                        )}
                    </div>
                )}
            </div>

            <InspectorControls>
                <PanelBody title={__('Display', 'smartpay')} initialOpen={true}>
                    <ToggleControl
                        __nextHasNoMarginBottom
                        label={__('Show progress bar', 'smartpay')}
                        checked={showBar}
                        onChange={(v) => setAttributes({ showBar: v })}
                    />
                    <ToggleControl
                        __nextHasNoMarginBottom
                        label={__('Show counts', 'smartpay')}
                        checked={showCounts}
                        onChange={(v) => setAttributes({ showCounts: v })}
                    />
                    <ToggleControl
                        __nextHasNoMarginBottom
                        label={__('Show percentage', 'smartpay')}
                        checked={showPercentage}
                        onChange={(v) => setAttributes({ showPercentage: v })}
                    />
                    <ToggleControl
                        __nextHasNoMarginBottom
                        label={__('Show goal-met message', 'smartpay')}
                        checked={showMessage}
                        onChange={(v) => setAttributes({ showMessage: v })}
                    />
                    <TextControl
                        __nextHasNoMarginBottom
                        label={__('Progress message', 'smartpay')}
                        help={__('Optional. Tokens: {current} {target} {percent} {unit}. Leave blank for the default count.', 'smartpay')}
                        value={messageTemplate}
                        onChange={(v) => setAttributes({ messageTemplate: v })}
                        placeholder="{current} of {target} {unit}"
                    />
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Preview fill %', 'smartpay')}
                        help={__('Editor preview only — the frontend shows real progress.', 'smartpay')}
                        value={previewPercent}
                        onChange={(v) => setAttributes({ previewPercent: v })}
                        min={0}
                        max={100}
                    />
                </PanelBody>

                <PanelColorSettings
                    title={__('Colors', 'smartpay')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: bgColor,
                            onChange: (v) => setAttributes({ bgColor: v || '' }),
                            label: __('Card background', 'smartpay'),
                        },
                        {
                            value: barColor,
                            onChange: (v) => setAttributes({ barColor: v || '' }),
                            label: __('Bar fill', 'smartpay'),
                        },
                        {
                            value: trackColor,
                            onChange: (v) => setAttributes({ trackColor: v || '' }),
                            label: __('Bar track', 'smartpay'),
                        },
                        {
                            value: textColor,
                            onChange: (v) => setAttributes({ textColor: v || '' }),
                            label: __('Text', 'smartpay'),
                        },
                    ]}
                />

                <PanelBody title={__('Styles & Spacing', 'smartpay')} initialOpen={false}>
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Bar height', 'smartpay')}
                        value={barHeight}
                        onChange={(v) => setAttributes({ barHeight: v })}
                        min={4}
                        max={40}
                    />
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Bar radius', 'smartpay')}
                        value={barRadius}
                        onChange={(v) => setAttributes({ barRadius: v })}
                        min={0}
                        max={20}
                    />
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Card radius', 'smartpay')}
                        value={cardRadius}
                        onChange={(v) => setAttributes({ cardRadius: v })}
                        min={0}
                        max={30}
                    />
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Padding', 'smartpay')}
                        value={padding}
                        onChange={(v) => setAttributes({ padding: v })}
                        min={0}
                        max={48}
                    />
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Font size', 'smartpay')}
                        value={fontSize}
                        onChange={(v) => setAttributes({ fontSize: v })}
                        min={10}
                        max={28}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    )
}
