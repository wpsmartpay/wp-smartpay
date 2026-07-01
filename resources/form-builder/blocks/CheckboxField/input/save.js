import { useBlockProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { fieldName, options, defaultValue } = attributes
    const blockProps = useBlockProps.save()
    return (
        <div {...blockProps}>
            {options.map((option, index) => (
                <div className="custom-control custom-checkbox" key={index}>
                    <input
                        type="checkbox"
                        id={`${fieldName}-${index}`}
                        name={`smartpay_form[${fieldName}][]`}
                        className="custom-control-input"
                        value={option.value}
                        defaultChecked={option.value === defaultValue}
                    />
                    <label className="custom-control-label ml-4" htmlFor={`${fieldName}-${index}`}>
                        {option.label}
                    </label>
                </div>
            ))}
        </div>
    )
}
