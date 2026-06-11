/**
 * Shared layout helpers for the Pricing block.
 *
 * The block uses the native flex `layout` support for its "justification" and
 * "orientation" controls. WordPress generates the matching CSS against the block
 * ROOT element (.smartpay-pricing) — but the actual flex row/column of cards is
 * the nested `.form-plan-grid`, so the native justify-content never reaches the
 * cards. These helpers forward the chosen justification onto `.form-plan-grid`.
 */

const JUSTIFY_MAP = {
    left: 'flex-start',
    center: 'center',
    right: 'flex-end',
    'space-between': 'space-between',
}

/**
 * Build the inline style for `.form-plan-grid` from the block's `layout` attr.
 *
 * @param {Object} layout The block `layout` attribute (may be undefined).
 * @return {Object} Style object (empty when no justification is set).
 */
export const gridJustifyStyle = (layout) => {
    const justify = layout?.justifyContent
    const value = justify ? JUSTIFY_MAP[justify] : ''
    return value ? { justifyContent: value } : {}
}
