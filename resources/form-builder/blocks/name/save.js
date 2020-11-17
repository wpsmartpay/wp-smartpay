import { __ } from '@wordpress/i18n'

export const save = ({ attributes }) => {
    return (
        <div className="form-element">
            <div className="form-row">
                <div className="col">
                    <label for="first_name">
                        {__('First Name', 'smartpay')}
                    </label>
                    <input
                        type="text"
                        className="form-control"
                        id="first_name"
                        name="first_name"
                    />
                </div>

                {attributes.showLastName && (
                    <div className="col">
                        <label for="last_name">
                            {__('Last Name', 'smartpay')}
                        </label>
                        <input
                            type="text"
                            className="form-control"
                            id="last_name"
                            name="last_name"
                        />
                    </div>
                )}
            </div>
        </div>
    )
}
