import { useBlockProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { fieldName, placeholder, isRequired } = attributes
    const blockProps = useBlockProps.save({ className: 'form-control' })
    return (
        <input
            {...blockProps}
            type="text"
            id={fieldName}
            name={`smartpay_form[name][${fieldName}]`}
            placeholder={placeholder}
            required={isRequired}
        />
    )
}
