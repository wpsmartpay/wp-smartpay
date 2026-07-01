import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

const TEMPLATE = [
    ['smartpay-form/radio-input-label'],
    ['smartpay-form/radio-input-input'],
]

const ALLOWED = ['smartpay-form/radio-input-label', 'smartpay-form/radio-input-input']

export const edit = () => {
    const blockProps = useBlockProps({ className: 'form-element' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ALLOWED,
        templateLock: 'all',
    })
    return <div {...innerBlocksProps} />
}
