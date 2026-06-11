import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

/**
 * Parent save — wraps the option cards + custom amount + coordination inputs.
 *
 * - is-style-grid|list|compact (block style) + native background land on the
 *   wrapper via useBlockProps.save.
 * - `.form-plan-grid` holds the child `.form-plan-card`s (InnerBlocks content).
 * - CSS vars (--sp-currency, --sp-plan-gap, --sp-input-*) drive symbol, gap and
 *   custom-input theming for the children + custom field.
 * - Global hidden inputs preserve the frontend form.js / checkout contract.
 */
export const save = ({ attributes }) => {
    const {
        preset,
        showDescription,
        allowCustomAmount,
        customAmountLabel,
        currencySymbol,
        gap,
        customInputBackground,
        customInputBorder,
    } = attributes

    const wrapperStyle = {
        '--sp-currency': `'${currencySymbol}'`,
    }
    if (gap) wrapperStyle['--sp-plan-gap'] = gap
    if (customInputBackground) wrapperStyle['--sp-input-bg'] = customInputBackground
    if (customInputBorder) wrapperStyle['--sp-input-border'] = customInputBorder

    // Only add `is-hide-desc` when explicitly turned off — keeping the default-on
    // markup byte-identical to previously-saved blocks (no validation breakage).
    const presetClass = `is-style-${preset || 'grid'}`
    const descClass = showDescription === false ? ' is-hide-desc' : ''
    const blockProps = useBlockProps.save({
        className: `form--amount-section smartpay-pricing ${presetClass}${descClass}`,
        style: wrapperStyle,
    })
    const innerProps = useInnerBlocksProps.save({ className: 'form-plan-grid' })

    return (
        <div {...blockProps}>
            {/* .form-amounts is the scope form.js binds selection + coordination to. */}
            <div className="form-amounts">
                <div {...innerProps} />

                <input
                    type="hidden"
                    name="smartpay_form_billing_type"
                    value="One Time"
                />
                <input
                    type="hidden"
                    name="smartpay_form_billing_period"
                    value="month"
                />

                {allowCustomAmount ? (
                    <div className="form-group custom-amount-wrapper m-0">
                        <label className="form-amounts--label d-block m-0 mb-2">
                            {customAmountLabel}
                        </label>
                        <div className="input-group mb-3">
                            <div className="input-group-prepend">
                                <span className="input-group-text px-3">
                                    {currencySymbol}
                                </span>
                            </div>
                            <input
                                type="text"
                                className="form-control form--custom-amount amount"
                                name="smartpay_form_amount"
                                value="0.00"
                                placeholder=""
                            />
                        </div>
                    </div>
                ) : (
                    <input
                        type="hidden"
                        className="form-control form--custom-amount amount"
                        name="smartpay_form_amount"
                        value="0.00"
                    />
                )}
            </div>
        </div>
    )
}
