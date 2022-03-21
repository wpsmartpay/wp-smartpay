import { __ } from '@wordpress/i18n'
import { Card, Form, Button } from 'react-bootstrap'

export const FormSettings = ({ form, setFormData }) => {
    // console.log(form.settings.payButtonLabel)

    const setSettingsData = (settings) => {
        setFormData({
            ...form,
            settings,
        })
        console.log(form.settings.payButtonLabel)
    }

    return (
        <Card>
            <Card.Body>
                <h2 className="m-0">{__('Form Settings', 'smartpay')}</h2>
                <div className="col-md-8 mx-auto">
                    <div className="form-group mb-0">
                        <label>
                            {__('Pay button label', 'smartpay')}
                        </label>
                        <Form.Control
                            className="mt-1"
                            size="sm"
                            type="text"
                            defaultValue={form.settings.payButtonLabel}
                            onChange={(e) => {
                                setSettingsData({
                                    ...form.settings,
                                    payButtonLabel: e.target.value,
                                })
                            }}
                            placeholder={__(
                                'Pay Now',
                                'smartpay'
                            )}
                        />
                    </div>
                </div>
            </Card.Body>
        </Card>
    )
}