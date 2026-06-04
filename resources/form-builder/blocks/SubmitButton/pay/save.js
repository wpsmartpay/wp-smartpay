/**
 * The Pay Button renders nothing inline. The form embed template reads this
 * block's attributes and renders the real button after the gateway selector,
 * so the pay action always sits last. Returning null keeps the attributes in
 * the saved content for the template to parse.
 */
export const save = () => null
