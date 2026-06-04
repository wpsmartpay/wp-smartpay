import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

const TEMPLATE = [
    ['smartpay-form/submit-coupon'],
    ['smartpay-form/submit-pay'],
]

const ALLOWED = ['smartpay-form/submit-coupon', 'smartpay-form/submit-pay']

/**
 * Container edit — just hosts the Coupon + Pay Button children. Removing the
 * Coupon child hides the coupon section; both children are single-use.
 */
export const edit = () => {
    const blockProps = useBlockProps({ className: 'smartpay-submit-area' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ALLOWED,
        templateLock: false,
    })
    return <div {...innerBlocksProps} />
}
