import { __ } from '@wordpress/i18n'
import { Card, Form, Button } from 'react-bootstrap'

export const FormSettings = ({ form, setFormData }) => {
    const setSettingsData = (settings) => {
        setFormData({
            ...form,
            settings,
        })
    }

    return (
        <Card>
            <Card.Body>
                <h2 className="m-0">{__('Checkout Options', 'smartpay')}</h2>
                <hr/>
                <div className="col-md-10 mt-4 mx-auto">
                    <Card className="bg-light">
                        <div className="p-3">
                            <div className="form-group mb-0">
                                <label>
                                    {__('Checkout label', 'smartpay')}
                                </label>
                                <Form.Control
                                    className="mt-2"
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
                    </Card>
                    <Card className="my-3 bg-light">
                        <div className="p-3">
                            <div className="custom-control custom-checkbox py-1">
                                <input
                                    type="checkbox"
                                    className="custom-control-input"
                                    id="allowExternalLinkOnPaymentSuccessPage"
                                    value="true"
                                    checked={form.settings.externalLink?.allowExternalLink}
                                    onChange={(e) => {
                                        setSettingsData({
                                            ...form.settings,
                                            externalLink:{
                                                ...form.settings.externalLink,
                                                allowExternalLink: e.target.checked,
                                            },
                                        })
                                    }}
                                />
                                <label
                                    className="custom-control-label pt-1"
                                    htmlFor="allowExternalLinkOnPaymentSuccessPage"
                                >
                                    {__('Add External Resource Link on Payment Success Page', 'smartpay')}
                                </label>
                            </div>
                            {form.settings.externalLink?.allowExternalLink &&
                                <div className="form-group mt-3 mb-0">
                                    <div className="d-flex">
                                        <div className="w-75 mr-4">
                                            <label>
                                                {__('External Link', 'smartpay')}
                                            </label>
                                            <Form.Control
                                                size="sm"
                                                type="text"
                                                className="mt-2"
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
                                                placeholder={__('https://resourcelink.com', 'smartpay')}
                                            />
                                        </div>
                                        <div className="w-25">
                                            <label>
                                                {__('Label', 'smartpay')}
                                            </label>
                                            <Form.Control
                                                size="sm"
                                                type="text"
                                                className="mt-2"
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
                                    {/*<p className="text-muted">* {__('It will show on payment receipt page.', 'smartpay')}</p>*/}
                                </div>
                            }
                        </div>
                    </Card>
                </div>
            </Card.Body>
        </Card>
    )
}