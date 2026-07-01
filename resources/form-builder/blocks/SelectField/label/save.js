import { useBlockProps, RichText } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const blockProps = useBlockProps.save()
    return <RichText.Content {...blockProps} tagName="label" value={attributes.text} />
}
