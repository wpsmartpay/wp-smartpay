import { __ } from '@wordpress/i18n'
import { Card, Form, Button } from 'react-bootstrap'

export const FormSettings = ({ form, setFormData }) => {
    const setSettingsData = (settings) => {
        console.log(settings)
        setFormData({
            ...form,
            settings,
        })
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
                    <div className="form-group mt-4 mb-0">
                        <div className="d-flex">
                            <div className="w-75 mr-4">
                                <label>
                                    {__('External Link', 'smartpay')}
                                </label>
                                <Form.Control
                                    size="sm"
                                    type="text"
                                    defaultValue={form.settings.externalLink?.link}
                                    onChange={(e) => {
                                        setSettingsData({
                                            ...form.settings,
                                            externalLink:{
                                                ...form.settings.externalLink,
                                                link: e.target.value,
                                            }
                                        })
                                    }}
                                    placeholder={__('https://example.com', 'smartpay')}
                                />
                            </div>
                            <div className="w-25">
                                <label>
                                    {__('Label', 'smartpay')}
                                </label>
                                <Form.Control
                                    size="sm"
                                    type="text"
                                    defaultValue={form.settings.externalLink?.label}
                                    onChange={(e) => {
                                        setSettingsData({
                                            ...form.settings,
                                            externalLink:{
                                                ...form.settings.externalLink,
                                                label: e.target.value,
                                            }
                                        })
                                    }}
                                    placeholder={__('Link Label', 'smartpay')}
                                />
                            </div>
                        </div>
                        <p className="text-muted">* {__('It will show on payment receipt page.', 'smartpay')}</p>
                    </div>
                </div>
            </Card.Body>
        </Card>
    )
}