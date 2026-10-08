import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { columns } = attributes
    const colClass = columns > 0 ? `sp-cols-${columns}` : ''
    const blockProps = useBlockProps.save({
        className: `smartpay-address${colClass ? ' ' + colClass : ''}`,
    })
    const innerBlocksProps = useInnerBlocksProps.save(blockProps)
    return <div {...innerBlocksProps} />
}
