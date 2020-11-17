import { Button } from '@wordpress/components'

import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <div className="form-group">
                <label for="smartpay_email">{__('Email', 'smartpay')}</label>
                <input
                    type="email"
                    className="form-control"
                    id="smartpay_email"
                    name="smartpay_email"
                />
            </div>
        </div>
    )
}
