import { useBlockProps } from '@wordpress/block-editor'

export const save = ({ attributes }) => {
    const { fieldName, options, defaultValue } = attributes
    const blockProps = useBlockProps.save()
    return (
        <div {...blockProps}>
            {options.map((option, index) => (
                <div className="custom-control custom-radio" key={index}>
                    <input
                        type="radio"
                        id={`${fieldName}-${index}`}
                        name={`smartpay_form[${fieldName}]`}
                        className="custom-control-input"
                        value={option.value}
                        checked={option.value === defaultValue ? true : undefined}
                    />
                    <label className="custom-control-label ml-4" for={`${fieldName}-${index}`}>
                        {option.label}
                    </label>
                </div>
            ))}
        </div>
    )
}
