/**
 * Goal Progress renders nothing inline — the counts are live, so the markup is
 * produced server-side by the render_block_smartpay-form/goal-progress filter
 * (see NativeForm) from this block's attributes. Returning null keeps the
 * block's attributes in the saved content for that filter to read.
 */
export const save = () => null
