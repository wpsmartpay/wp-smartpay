import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <label for={attributes.attributes.name}>
                {attributes.settings.label}
            </label>
            <select
                id={attributes.attributes.name}
                name={`smartpay_form[${attributes.attributes.name}]`}
                class="form-control"
            >
                {attributes.attributes.options.map((option, index) => {
                    return (
                        <option key={index} value={option.value}>
                            {option.label}
                        </option>
                    )
                })}
            </select>
        </div>
    )
}
