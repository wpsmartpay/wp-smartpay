import { useBlockProps, RichText } from '@wordpress/block-editor'

/**
 * Child save — one `.form-plan-card`.
 *
 * useBlockProps.save() puts this option's native color/border/typography/spacing
 * onto the card wrapper. Markup + field names match the frontend contract so the
 * shared `form.js` selection logic binds unchanged. The currency symbol is drawn
 * from the `--sp-currency` CSS variable set by the parent (save can't read context).
 */
export const save = ({ attributes }) => {
    const { key, label, amount, billing_type, billing_period } = attributes
    const billingType = billing_type || 'One Time'
    const isSub = billingType !== 'One Time'
    const blockProps = useBlockProps.save({ className: 'form-plan-card plan-amount' })

    return (
        <label {...blockProps}>
            <input
                type="radio"
                name="_form_amount"
                id={`_form_amount_${key}`}
                className="radio"
                value={amount}
            />
            <span className="plan-details" aria-hidden="true">
                <RichText.Content tagName="span" className="plan-type" value={label} />
                <span className="plan-cost">
                    <span className="plan-symbol" />
                    {amount}
                    {isSub && billing_period && (
                        <>
                            <span className="slash">/</span>
                            <span className="plan-cycle">{billing_period}</span>
                        </>
                    )}
                </span>
            </span>
            <input type="hidden" name="_form_billing_type" value={billingType} />
            <input type="hidden" name="_form_amount_key" value={key} />
            {isSub && billing_period && (
                <input type="hidden" name="_form_billing_period" value={billing_period} />
            )}
        </label>
    )
}
