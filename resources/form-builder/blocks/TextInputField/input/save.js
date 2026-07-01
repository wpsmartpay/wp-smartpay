import { useBlockProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { fieldName, inputType, placeholder, isRequired } = attributes
    const blockProps = useBlockProps.save({ className: 'form-control' })
    return (
        <input
            {...blockProps}
            type={inputType || 'text'}
            id={fieldName}
            name={`smartpay_form[${fieldName}]`}
            placeholder={placeholder}
            required={isRequired}
        />
    )
}
