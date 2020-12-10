import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <label for={attributes.attributes.name}>
                {attributes.settings.label}
            </label>
            {attributes.attributes.options.map((option, index) => {
                return (
                    <div class="custom-control custom-checkbox" key={index}>
                        <input
                            type="checkbox"
                            id={`${attributes.attributes.name}-${index}`}
                            name={`smartpay_form[${attributes.attributes.name}]`}
                            class="custom-control-input"
                            value={option.value}
                            checked={
                                option.value ===
                                attributes.attributes.defaultValue
                            }
                        />
                        <label
                            class="custom-control-label ml-4"
                            for={`${attributes.attributes.name}-${index}`}
                        >
                            {option.label}
                        </label>
                    </div>
                )
            })}
        </div>
    )
}
