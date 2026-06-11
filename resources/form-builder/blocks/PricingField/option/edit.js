import {
    PanelBody,
    TextControl,
    SelectControl,
    Notice,
    ExternalLink,
    Flex,
    FlexItem,
} from '@wordpress/components'
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor'
import { Icon, lock } from '@wordpress/icons'
import { __ } from '@wordpress/i18n'
import { useEffect } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

const DEFAULT_UPGRADE_URL = 'https://wpsmartpay.com/pricing/?utm_source=free-plugin&utm_medium=pricing-block&utm_campaign=upgrade-to-pro'

const BILLING_PERIODS = [
    { label: __('Daily', 'smartpay'), value: 'day' },
    { label: __('Weekly', 'smartpay'), value: 'week' },
    { label: __('Monthly', 'smartpay'), value: 'month' },
    { label: __('Yearly', 'smartpay'), value: 'year' },
]

/**
 * Read the Pro flag (set by the server via wp_localize_script). Walks
 * window → parent → top because the block edit runs inside the editor iframe,
 * and treats wp_localize_script's "1"/"" string coercion as truthy.
 */
const readProFlag = () => {
    const truthy = (v) => v === true || v === 1 || v === '1'
    const frames = []
    try { frames.push(window) } catch (e) {}
    try { if (window.parent && window.parent !== window) frames.push(window.parent) } catch (e) {}
    try { if (window.top && window.top !== window.parent) frames.push(window.top) } catch (e) {}
    for (const w of frames) {
        try {
            const d = w.smartpayPricingData
            if (d && typeof d.isPro !== 'undefined') {
                return { isPro: truthy(d.isPro), upgradeUrl: DEFAULT_UPGRADE_URL }
            }
        } catch (e) {}
    }
    return { isPro: false, upgradeUrl: DEFAULT_UPGRADE_URL }
}

const generateKey = () => 'opt-' + Math.random().toString(36).substr(2, 9)

export const edit = ({ attributes, setAttributes }) => {
    const { key, label, description, amount, billing_type, billing_period, setup_fee, billing_cycle } = attributes
    const { isPro: pro, upgradeUrl } = readProFlag()
    const isSub = billing_type === 'Subscription'

    // Ensure a stable key exists.
    useEffect(() => {
        if (!key) setAttributes({ key: generateKey() })
    }, [])

    let billingTypes = [{ label: __('One Time', 'smartpay'), value: 'One Time' }]
    if (pro) billingTypes.push({ label: __('Subscription', 'smartpay'), value: 'Subscription' })
    billingTypes = applyFilters('smartpay.pricing_option.billing_types', billingTypes, { pro })

    const blockProps = useBlockProps({
        className: 'form-plan-card plan-amount',
    })

    return (
        <>
            <label {...blockProps}>
                {/* Visual-only radio so the builder canvas matches the frontend
                    (real radio is rendered by save.js). Hidden in Grid via CSS. */}
                <span className="radio" aria-hidden="true" />
                <span className="plan-details">
                    <RichText
                        tagName="span"
                        className="plan-type"
                        value={label}
                        allowedFormats={[]}
                        onChange={(v) => setAttributes({ label: v })}
                        placeholder={__('Plan name', 'smartpay')}
                    />
                    <RichText
                        tagName="span"
                        className="plan-desc"
                        value={description}
                        allowedFormats={[]}
                        onChange={(v) => setAttributes({ description: v })}
                        placeholder={__('Short description (optional)', 'smartpay')}
                    />
                    <span className="plan-cost">
                        <span className="plan-symbol" />
                        <input
                            type="number"
                            className="plan-amount-input"
                            step="0.01"
                            min="0"
                            value={amount}
                            onChange={(e) => setAttributes({ amount: e.target.value })}
                        />
                        {isSub && billing_period && (
                            <span className="plan-cycle">/{billing_period}</span>
                        )}
                    </span>
                </span>
            </label>

            <InspectorControls>
                <PanelBody title={__('Option', 'smartpay')} initialOpen={true}>
                    <TextControl
                        label={__('Amount Label', 'smartpay')}
                        value={label}
                        onChange={(v) => setAttributes({ label: v })}
                        __nextHasNoMarginBottom
                    />
                    <TextControl
                        label={__('Description', 'smartpay')}
                        help={__('Shown under the label in the list layout.', 'smartpay')}
                        value={description}
                        onChange={(v) => setAttributes({ description: v })}
                        __nextHasNoMarginBottom
                    />
                    <Flex>
                        <FlexItem isBlock>
                            <TextControl
                                label={__('Amount', 'smartpay')}
                                type="number"
                                step="0.01"
                                min="0"
                                value={amount}
                                onChange={(v) => setAttributes({ amount: v })}
                                __nextHasNoMarginBottom
                            />
                        </FlexItem>
                        <FlexItem isBlock>
                            <SelectControl
                                label={__('Billing Type', 'smartpay')}
                                value={billing_type}
                                options={billingTypes}
                                onChange={(v) => setAttributes({ billing_type: v })}
                                __nextHasNoMarginBottom
                            />
                        </FlexItem>
                    </Flex>

                    {!pro && (
                        <div style={{ marginTop: '12px' }}>
                            <Notice status="info" isDismissible={false}>
                                <Flex align="center" justify="flex-start" gap={2}>
                                    <Icon icon={lock} size={18} />
                                    <span>{__('Subscription is available in the Pro plan.', 'smartpay')}</span>
                                </Flex>
                                <ExternalLink href={upgradeUrl}>
                                    {__('Upgrade to Pro', 'smartpay')}
                                </ExternalLink>
                            </Notice>
                        </div>
                    )}

                    {pro && isSub && (
                        <div style={{ marginTop: '12px' }}>
                            <Flex>
                                <FlexItem isBlock>
                                    <SelectControl
                                        label={__('Billing Period', 'smartpay')}
                                        value={billing_period || 'month'}
                                        options={BILLING_PERIODS}
                                        onChange={(v) => setAttributes({ billing_period: v })}
                                        __nextHasNoMarginBottom
                                    />
                                </FlexItem>
                                <FlexItem isBlock>
                                    <TextControl
                                        label={__('Setup Fee', 'smartpay')}
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={setup_fee || '0'}
                                        onChange={(v) => setAttributes({ setup_fee: v })}
                                        __nextHasNoMarginBottom
                                    />
                                </FlexItem>
                            </Flex>
                            <TextControl
                                label={__('Billing Cycles', 'smartpay')}
                                type="number"
                                min="0"
                                value={billing_cycle || ''}
                                placeholder={__('Unlimited', 'smartpay')}
                                onChange={(v) => setAttributes({ billing_cycle: v })}
                                __nextHasNoMarginBottom
                            />
                        </div>
                    )}
                </PanelBody>
            </InspectorControls>
        </>
    )
}
