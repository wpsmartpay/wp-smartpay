import { useBlockProps, RichText } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'

export const edit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps()
    return (
        <RichText
            {...blockProps}
            tagName="label"
            value={attributes.text}
            allowedFormats={[]}
            onChange={(text) => setAttributes({ text })}
            placeholder={__('Label', 'smartpay')}
        />
    )
}
