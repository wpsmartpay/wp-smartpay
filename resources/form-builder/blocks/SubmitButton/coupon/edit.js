import { PanelBody, TextControl } from '@wordpress/components'
import {
    InspectorControls,
    PanelColorSettings,
    useBlockProps,
} from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'

/**
 * Editor preview of the coupon section. Shows both the "Have a coupon?" toggle
 * and the revealed input row at once so the author can style/word both. The
 * real frontend markup (with AJAX) is rendered by the Coupon module from these
 * attributes.
 */
export const edit = ({ attributes, setAttributes }) => {
    const { toggleLabel, placeholder, applyLabel, accentColor } = attributes
    const accent = accentColor || '#28a745'

    const blockProps = useBlockProps({ className: 'smartpay-submit-coupon-block' })

    return (
        <>
            <div {...blockProps}>
                <button type="button" className="smartpay-coupon-preview__toggle" style={{ color: accent }}>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z" />
                        <path d="M9 6v12" strokeDasharray="2 2" />
                    </svg>
                    <span>{toggleLabel || __('Have a coupon?', 'smartpay')}</span>
                </button>

                <div className="smartpay-coupon-preview__row">
                    <span className="smartpay-coupon-preview__input">
                        {placeholder || __('Coupon code', 'smartpay')}
                    </span>
                    <span className="smartpay-coupon-preview__apply" style={{ background: accent }}>
                        {applyLabel || __('Apply', 'smartpay')}
                    </span>
                    <span className="smartpay-coupon-preview__close" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </span>
                </div>
            </div>

            <InspectorControls>
                <PanelBody title={__('Coupon', 'smartpay')} initialOpen={true}>
                    <p className="components-base-control__help" style={{ marginTop: 0 }}>
                        {__('Requires "Enable coupons at form" in SmartPay settings. Remove this block to hide the coupon section.', 'smartpay')}
                    </p>
                    <TextControl
                        __nextHasNoMarginBottom
                        label={__('Toggle label', 'smartpay')}
                        value={toggleLabel}
                        onChange={(v) => setAttributes({ toggleLabel: v })}
                        placeholder={__('Have a coupon?', 'smartpay')}
                    />
                    <TextControl
                        __nextHasNoMarginBottom
                        label={__('Input placeholder', 'smartpay')}
                        value={placeholder}
                        onChange={(v) => setAttributes({ placeholder: v })}
                        placeholder={__('Coupon code', 'smartpay')}
                    />
                    <TextControl
                        __nextHasNoMarginBottom
                        label={__('Apply button label', 'smartpay')}
                        value={applyLabel}
                        onChange={(v) => setAttributes({ applyLabel: v })}
                        placeholder={__('Apply', 'smartpay')}
                    />
                </PanelBody>

                <PanelColorSettings
                    title={__('Color', 'smartpay')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: accentColor,
                            onChange: (v) => setAttributes({ accentColor: v || '' }),
                            label: __('Accent', 'smartpay'),
                        },
                    ]}
                />
            </InspectorControls>
        </>
    )
}
