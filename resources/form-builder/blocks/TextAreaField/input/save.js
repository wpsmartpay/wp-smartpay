import { useBlockProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { fieldName, placeholder, isRequired, rows } = attributes
    const blockProps = useBlockProps.save({ className: 'form-control' })
    return (
        <textarea
            {...blockProps}
            id={fieldName}
            name={`smartpay_form[${fieldName}]`}
            placeholder={placeholder}
            required={isRequired}
            rows={rows}
        ></textarea>
    )
}
