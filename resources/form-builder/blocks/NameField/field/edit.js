import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

const TEMPLATE = [
    ['smartpay-form/name-label'],
    ['smartpay-form/name-input'],
]

export const edit = () => {
    const blockProps = useBlockProps({ className: 'col' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ['smartpay-form/name-label', 'smartpay-form/name-input'],
        templateLock: 'all',
    })
    return <div {...innerBlocksProps} />
}
