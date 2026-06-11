import { useBlockProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { fieldName, options, defaultValue } = attributes
    const blockProps = useBlockProps.save({ className: 'form-control' })
    return (
        <select {...blockProps} id={fieldName} name={`smartpay_form[${fieldName}]`}>
            {options.map((option, index) => (
                <option
                    key={index}
                    value={option.value}
                    selected={option.value === defaultValue ? true : undefined}
                >
                    {option.label}
                </option>
            ))}
        </select>
    )
}
