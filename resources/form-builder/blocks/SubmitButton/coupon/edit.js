import { PanelBody, TextControl, Notice } from '@wordpress/components'
import {
    InspectorControls,
    PanelColorSettings,
    useBlockProps,
} from '@wordpress/block-editor'
import { useSelect } from '@wordpress/data'
import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/** Read the per-form "Enable Coupon" setting from post meta. */
const useCouponEnabled = () =>
    useSelect((select) => {
        const meta = select('core/editor')?.getEditedPostAttribute('meta') || {}
        try {
            const s = JSON.parse(meta._smartpay_settings || '{}')
            return !!s.enable_coupon
        } catch {
            return false
        }
    }, [])

const TicketIcon = () => (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z" />
        <path d="M9 6v12" strokeDasharray="2 2" />
    </svg>
)

const CrossIcon = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M18 6 6 18M6 6l12 12" />
    </svg>
)

/**
 * Editor preview of the coupon section — interactive, mirroring the frontend:
 * the toggle text shows first; clicking it expands the input + Apply + close
 * row; clicking the close collapses back to the text. The real frontend markup
 * (with AJAX) is rendered by the Coupon module from these attributes; visibility
 * is controlled by Form Settings → Enable Coupon.
 */
export const edit = ({ attributes, setAttributes }) => {
    const { toggleLabel, placeholder, applyLabel, accentColor } = attributes
    const accent = accentColor || '#28a745'
    const [open, setOpen] = useState(false)
    const enabled = useCouponEnabled()

    const blockProps = useBlockProps({ className: 'smartpay-submit-coupon-block' })

    if (!enabled) {
        return (
            <div {...blockProps}>
                <Notice status="info" isDismissible={false}>
                    {__('Coupon is off. Turn it on in Form Settings → Enable Coupon.', 'smartpay')}
                </Notice>
            </div>
        )
    }

    return (
        <>
            <div {...blockProps}>
                {/* The toggle text always stays visible (mirrors the frontend
                    link); clicking it reveals/hides the row below it. */}
                <button
                    type="button"
                    className="smartpay-coupon-preview__toggle"
                    style={{ color: accent }}
                    onClick={() => setOpen((v) => !v)}
                >
                    <TicketIcon />
                    <span>{toggleLabel || __('Have a coupon?', 'smartpay')}</span>
                </button>
                {open && (
                    <div className="smartpay-coupon-preview__row">
                        <span className="smartpay-coupon-preview__input">
                            {placeholder || __('Coupon code', 'smartpay')}
                        </span>
                        <span className="smartpay-coupon-preview__apply" style={{ background: accent }}>
                            {applyLabel || __('Apply', 'smartpay')}
                        </span>
                        <button
                            type="button"
                            className="smartpay-coupon-preview__close"
                            aria-label={__('Cancel', 'smartpay')}
                            onClick={() => setOpen(false)}
                        >
                            <CrossIcon />
                        </button>
                    </div>
                )}
            </div>

            <InspectorControls>
                <PanelBody title={__('Coupon', 'smartpay')} initialOpen={true}>
                    <p className="components-base-control__help" style={{ marginTop: 0 }}>
                        {__('Turn the coupon on/off in Form Settings → Enable Coupon. This block controls its wording + style.', 'smartpay')}
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
