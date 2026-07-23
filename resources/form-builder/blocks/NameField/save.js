import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

export const save = () => {
    const blockProps = useBlockProps.save({ className: 'form-element row' })
    const innerBlocksProps = useInnerBlocksProps.save(blockProps)
    return <div {...innerBlocksProps} />
}
