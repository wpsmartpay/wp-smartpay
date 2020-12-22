import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element row">
            {attributes.fields.map((field, index) => {
                return (
                    !!field.settings.visible && (
                        <div className="col" key={index}>
                            <label for={field.attributes.name}>
                                {field.settings.label}
                            </label>
                            <input
                                type="text"
                                id={field.attributes.name}
                                name={`smartpay_form[${attributes.attributes.name}][${field.attributes.name}]`}
                                className="form-control"
                                placeholder={field.attributes.placeholder}
                                required={field.attributes.isRequired}
                                value=""
                            />
                        </div>
                    )
                )
            })}
        </div>
    )
}
