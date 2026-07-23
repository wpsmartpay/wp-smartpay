import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

const TEMPLATE = [
    ['smartpay-form/email-label'],
    ['smartpay-form/email-input'],
]

const ALLOWED = ['smartpay-form/email-label', 'smartpay-form/email-input']

/**
 * Container edit — hosts the Label + Input children. Structure is locked so the
 * field always has exactly one label and one input; both stay individually
 * editable + stylable.
 */
export const edit = () => {
    const blockProps = useBlockProps({ className: 'form-element' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ALLOWED,
        templateLock: 'all',
    })
    return <div {...innerBlocksProps} />
}
