import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

const TEMPLATE = [
    ['smartpay-form/textarea-input-label'],
    ['smartpay-form/textarea-input-input'],
]

const ALLOWED = ['smartpay-form/textarea-input-label', 'smartpay-form/textarea-input-input']

export const edit = () => {
    const blockProps = useBlockProps({ className: 'form-element' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ALLOWED,
        templateLock: 'all',
    })
    return <div {...innerBlocksProps} />
}
