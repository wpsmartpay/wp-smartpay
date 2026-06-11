import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

export const save = () => {
    const blockProps = useBlockProps.save({ className: 'col' })
    const innerBlocksProps = useInnerBlocksProps.save(blockProps)
    return <div {...innerBlocksProps} />
}
