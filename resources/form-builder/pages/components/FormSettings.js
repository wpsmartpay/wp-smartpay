import { __ } from '@wordpress/i18n'
import { Card, CardBody, CardHeader, TextControl, CheckboxControl } from '@wordpress/components'

export const FormSettings = ({ form, setFormData }) => {
    const setSettingsData = (settings) => {
        setFormData({
            ...form,
            settings,
        })
    }

    return (
        <Card className="smartpay-card">
            <CardHeader>
                <h2 className="smartpay-card__title">{__('Checkout Options', 'smartpay')}</h2>
            </CardHeader>
            <CardBody>
                <div className="smartpay-settings-section">
                    <Card className="smartpay-settings-card">
                        <CardBody>
                            <TextControl
                                label={__('Checkout label', 'smartpay')}
                                value={form.settings.payButtonLabel || ''}
                                onChange={(value) => {
                                    setSettingsData({
                                        ...form.settings,
                                        payButtonLabel: value,
                                    })
                                }}
                                placeholder={__('Pay Now', 'smartpay')}
                                __nextHasNoMarginBottom
                            />
                        </CardBody>
                    </Card>

                    <Card className="smartpay-settings-card">
                        <CardBody>
                            <CheckboxControl
                                label={__('Add External Resource Link on Payment Success Page', 'smartpay')}
                                checked={form.settings.externalLink?.allowExternalLink || false}
                                onChange={(checked) => {
                                    setSettingsData({
                                        ...form.settings,
                                        externalLink: {
                                            ...form.settings.externalLink,
                                            allowExternalLink: checked,
                                        },
                                    })
                                }}
                                __nextHasNoMarginBottom
                            />
                            {form.settings.externalLink?.allowExternalLink && (
                                <div className="smartpay-settings-external-link">
                                    <div className="smartpay-settings-external-link__fields">
                                        <div className="smartpay-settings-external-link__url">
                                            <TextControl
                                                label={__('External Link', 'smartpay')}
                                                value={form.settings.externalLink?.link || ''}
                                                onChange={(value) => {
                                                    setSettingsData({
                                                        ...form.settings,
                                                        externalLink: {
                                                            ...form.settings.externalLink,
                                                            link: value,
                                                        },
                                                    })
                                                }}
                                                placeholder={__('https://resourcelink.com', 'smartpay')}
                                                __nextHasNoMarginBottom
                                            />
                                        </div>
                                        <div className="smartpay-settings-external-link__label">
                                            <TextControl
                                                label={__('Label', 'smartpay')}
                                                value={form.settings.externalLink?.label || ''}
                                                onChange={(value) => {
                                                    setSettingsData({
                                                        ...form.settings,
                                                        externalLink: {
                                                            ...form.settings.externalLink,
                                                            label: value,
                                                        },
                                                    })
                                                }}
                                                placeholder={__('Link Label', 'smartpay')}
                                                __nextHasNoMarginBottom
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}
                        </CardBody>
                    </Card>
                </div>
            </CardBody>
        </Card>
    )
}