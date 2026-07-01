import { useBlockProps, RichText } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const blockProps = useBlockProps.save()
    // htmlFor only rendered when set, so pre-existing labels stay byte-identical.
    return (
        <RichText.Content
            {...blockProps}
            tagName="label"
            htmlFor={attributes.htmlFor || undefined}
            value={attributes.text}
        />
    )
}
