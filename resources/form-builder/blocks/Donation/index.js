import { Notice, PanelBody, TextControl } from '@wordpress/components'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { useEntityProp } from '@wordpress/core-data'
import { useSelect } from '@wordpress/data'
import { __ } from '@wordpress/i18n'
import { commentContent, starFilled, seen, pageBreak } from '@wordpress/icons'
import './editor.scss'

/**
 * Donation blocks. All are dynamic: save() renders nothing and the markup is
 * produced server-side (app/Modules/Campaign/DonationFields.php), so the edit
 * views below are previews plus label settings.
 */

const Labels = ({ fields, attributes, setAttributes }) => (
    <InspectorControls>
        <PanelBody title={__('Labels', 'smartpay')}>
            {fields.map(([key, label]) => (
                <TextControl
                    key={key}
                    __nextHasNoMarginBottom
                    label={label}
                    value={attributes[key] || ''}
                    onChange={(v) => setAttributes({ [key]: v })}
                />
            ))}
        </PanelBody>
    </InspectorControls>
)

const base = (settings) => ({
    category: 'wp-smartpay',
    supports: { html: false, reusable: false, customClassName: false, ...(settings.supports || {}) },
    save: () => null,
    ...settings,
})

export const DonationAnonymous = {
    namespace: 'smartpay-form/donation-anonymous',
    settings: base({
        title: __('Give Anonymously', 'smartpay'),
        description: __('Checkbox so the donor’s name never appears publicly. The gift still counts toward every total.', 'smartpay'),
        icon: seen,
        keywords: ['donation', 'anonymous', 'privacy'],
        supports: { multiple: false },
        attributes: { label: { type: 'string', default: '' } },
        edit: ({ attributes, setAttributes }) => (
            <div {...useBlockProps({ className: 'sp-donation-preview' })}>
                <Labels attributes={attributes} setAttributes={setAttributes} fields={[['label', __('Label', 'smartpay')]]} />
                <label className="sp-donation-preview__check">
                    <input type="checkbox" disabled /> {attributes.label || __('Give anonymously — your name won’t appear publicly', 'smartpay')}
                </label>
            </div>
        ),
    }),
}

export const DonationComment = {
    namespace: 'smartpay-form/donation-comment',
    settings: base({
        title: __('Donor Comment', 'smartpay'),
        description: __('A public message the donor leaves with their gift.', 'smartpay'),
        icon: commentContent,
        keywords: ['donation', 'comment', 'message'],
        supports: { multiple: false },
        attributes: {
            label: { type: 'string', default: '' },
            placeholder: { type: 'string', default: '' },
            help: { type: 'string', default: '' },
        },
        edit: ({ attributes, setAttributes }) => (
            <div {...useBlockProps({ className: 'sp-donation-preview' })}>
                <Labels
                    attributes={attributes}
                    setAttributes={setAttributes}
                    fields={[
                        ['label', __('Label', 'smartpay')],
                        ['placeholder', __('Placeholder', 'smartpay')],
                        ['help', __('Help text', 'smartpay')],
                    ]}
                />
                <p className="sp-donation-preview__label">{attributes.label || __('Leave a message (optional)', 'smartpay')}</p>
                <textarea disabled rows={3} placeholder={attributes.placeholder} className="sp-donation-preview__input" />
            </div>
        ),
    }),
}

export const DonationTribute = {
    namespace: 'smartpay-form/donation-tribute',
    settings: base({
        title: __('Tribute (In Honor / In Memory)', 'smartpay'),
        description: __('Lets the donor dedicate the gift to someone.', 'smartpay'),
        icon: starFilled,
        keywords: ['donation', 'tribute', 'honor', 'memory', 'dedication'],
        supports: { multiple: false },
        attributes: { label: { type: 'string', default: '' } },
        edit: ({ attributes, setAttributes }) => (
            <div {...useBlockProps({ className: 'sp-donation-preview' })}>
                <Labels attributes={attributes} setAttributes={setAttributes} fields={[['label', __('Label', 'smartpay')]]} />
                <p className="sp-donation-preview__label">▸ {attributes.label || __('Dedicate this gift (in honor / in memory)', 'smartpay')}</p>
            </div>
        ),
    }),
}

/** The front end ignores breaks that are nested or on a Split-layout form; say so here. */
function StepBreakEdit({ attributes, setAttributes, clientId }) {
    const [meta] = useEntityProp('postType', 'smartpay_form', 'meta')
    const nested = useSelect((select) => select('core/block-editor').getBlockParents(clientId).length > 0, [clientId])
    let split = false
    try {
        split = 'split' === JSON.parse(meta?._smartpay_settings || '{}')?.checkout_layout
    } catch {}

    return (
        <div {...useBlockProps({ className: 'sp-donation-preview sp-donation-preview--break' })}>
            <Labels
                attributes={attributes}
                setAttributes={setAttributes}
                fields={[
                    ['nextLabel', __('Button label', 'smartpay')],
                    ['title', __('Next step title (optional)', 'smartpay')],
                ]}
            />
            {nested && (
                <Notice status="warning" isDismissible={false}>
                    {__('Steps only work at the top level of the form. Move this Step Break out of the Group or Columns block.', 'smartpay')}
                </Notice>
            )}
            {split && (
                <Notice status="warning" isDismissible={false}>
                    {__('Steps are not used with the Split checkout layout. Switch to Stacked in Form Settings to use them.', 'smartpay')}
                </Notice>
            )}
            <span>{__('Step break', 'smartpay')} · {attributes.nextLabel || __('Continue', 'smartpay')} →</span>
        </div>
    )
}

export const StepBreak = {
    namespace: 'smartpay-form/step-break',
    settings: base({
        title: __('Step Break', 'smartpay'),
        description: __('Splits the form into steps. Fields above this block are one step; the payment is always the last step.', 'smartpay'),
        icon: pageBreak,
        keywords: ['step', 'multi-step', 'page break', 'wizard'],
        attributes: {
            nextLabel: { type: 'string', default: '' },
            title: { type: 'string', default: '' },
        },
        edit: StepBreakEdit,
    }),
}

export const donationBlocks = [DonationAnonymous, DonationComment, DonationTribute, StepBreak]
