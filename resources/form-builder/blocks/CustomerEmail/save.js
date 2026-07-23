import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

/**
 * Wraps the Label + Input children in the `.form-element` div the frontend
 * form CSS expects. The children render the real label + input markup.
 */
export const save = () => {
    const blockProps = useBlockProps.save({ className: 'form-element' })
    const innerBlocksProps = useInnerBlocksProps.save(blockProps)
    return <div {...innerBlocksProps} />
}
