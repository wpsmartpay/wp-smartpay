import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

export const save = () => {
    const blockProps = useBlockProps.save({ className: 'smartpay-address' })
    const innerBlocksProps = useInnerBlocksProps.save(blockProps)
    return <div {...innerBlocksProps} />
}
