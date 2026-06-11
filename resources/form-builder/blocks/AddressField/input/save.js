import { useBlockProps } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { COUNTRIES, DEFAULT_STATES } from '../data/locations'

export const save = ({ attributes }) => {
    const { fieldName, fieldType, placeholder, isRequired } = attributes
    const blockProps = useBlockProps.save({ className: 'form-control' })
    const name = `smartpay_form[address][${fieldName}]`

    if (fieldType === 'country' || fieldType === 'state') {
        const options = fieldType === 'country' ? COUNTRIES : DEFAULT_STATES
        const prompt =
            placeholder ||
            (fieldType === 'country'
                ? __('Select country', 'smartpay')
                : __('Select state', 'smartpay'))
        return (
            <select {...blockProps} id={fieldName} name={name} required={isRequired}>
                <option value="">{prompt}</option>
                {options.map((o) => (
                    <option key={o.code} value={o.code}>
                        {o.name}
                    </option>
                ))}
            </select>
        )
    }

    return (
        <input
            {...blockProps}
            type="text"
            id={fieldName}
            name={name}
            placeholder={placeholder}
            required={isRequired}
        />
    )
}
