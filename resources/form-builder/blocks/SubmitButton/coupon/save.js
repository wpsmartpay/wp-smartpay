/**
 * The Coupon block renders nothing inline. The Coupon module reads these
 * attributes and renders the real coupon section (with AJAX apply wiring)
 * before the pay button. Returning null keeps the attributes in saved content.
 */
export const save = () => null
