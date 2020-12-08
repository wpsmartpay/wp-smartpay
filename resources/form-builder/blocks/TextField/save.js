import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <label for="smartpay_first_name">{attributes}</label>
            <input
                type="text"
                className="form-control"
                id="smartpay_first_name"
                name="smartpay_first_name"
            />
        </div>
    )
}
