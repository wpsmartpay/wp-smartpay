import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

const TEMPLATE = [
    ['smartpay-form/address-label'],
    ['smartpay-form/address-input-field'],
]

export const edit = () => {
    const blockProps = useBlockProps({ className: 'form-element' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ['smartpay-form/address-label', 'smartpay-form/address-input-field'],
        templateLock: 'all',
    })
    return <div {...innerBlocksProps} />
}
