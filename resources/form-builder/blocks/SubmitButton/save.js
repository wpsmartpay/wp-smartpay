import { InnerBlocks } from '@wordpress/block-editor'

/**
 * Serializes the child blocks (Coupon + Pay Button) without a wrapper. The
 * children render nothing on the frontend (their save() is null) — the form
 * embed template + Coupon module read their attributes and render the real
 * markup after the gateway selector.
 */
export const save = () => <InnerBlocks.Content />
