import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <label for={attributes.attributes.name}>
                {attributes.settings.label}
            </label>
            <textarea
                className={`form-control ${attributes.attributes.class}`}
                id={attributes.attributes.name}
                name={`smartpay_form[${attributes.attributes.name}]`}
                required={attributes.attributes.isRequired}
                placeholder={attributes.attributes.placeholder}
                value={attributes.attributes.value}
                rows={attributes.attributes.rows}
            ></textarea>
        </div>
    )
}
