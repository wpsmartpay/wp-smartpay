/**
 * The Pay Button renders nothing inline. The form embed template reads this
 * block's attributes and renders the real button after the gateway selector,
 * so the pay action always sits last. Returning null keeps the block's
 * attributes in the saved content (as a self-closing block comment) for the
 * template to parse, without emitting frontend markup here.
 */
export const save = () => null
