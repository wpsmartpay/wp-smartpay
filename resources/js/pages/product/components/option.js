import {Card, Form} from "react-bootstrap";
import {__} from "@wordpress/i18n";

export const OptionComponent = ({product, setProductData}) => {
    const _setSettingsData = (settings) => {
        console.log(settings)
        setProductData({
            ...product,
            settings,
        })
    }
    return (
        <Card className="p-4">
            <Card.Body>
                <div className="col-md-12 mx-auto">
                    <div className="form-group mb-0">
                        <label>
                            {__('Get it now button label label', 'smartpay')}
                        </label>
                        <Form.Control
                            className="mt-1"
                            size="sm"
                            type="text"
                            defaultValue={product.settings?.payButtonLabel}
                            placeholder={__(
                                'Get it now',
                                'smartpay'
                            )}
                            onChange={(e) => {
                                _setSettingsData({
                                    ...product.settings,
                                    payButtonLabel: e.target.value,
                                })
                            }}
                        />
                    </div>
                    <div className="custom-control custom-checkbox py-1 mt-4">
                        <input
                            type="checkbox"
                            className="custom-control-input"
                            id="allowExternalResourceLinkOnPaymentSuccessPage"
                            value="true"
                            /*checked={form.settings.externalLink?.allowExternalLink}
                            onChange={(e) => {
                                setSettingsData({
                                    ...form.settings,
                                    externalLink:{
                                        ...form.settings.externalLink,
                                        allowExternalLink: e.target.checked,
                                    },
                                })
                            }}*/
                        />
                        <label
                            className="custom-control-label pt-1"
                            htmlFor="allowExternalResourceLinkOnPaymentSuccessPage"
                        >
                            {__('Add External Link on Success Page', 'smartpay')}
                        </label>
                    </div>
                        <div className="form-group mt-4 mb-0">
                            <div className="d-flex">
                                <div className="w-75 mr-4">
                                    <label>
                                        {__('External Resource Link', 'smartpay')}
                                    </label>
                                    <Form.Control
                                        size="sm"
                                        type="text"
                                        /*defaultValue={form.settings.externalLink?.link}
                                        onChange={(e) => {
                                            setSettingsData({
                                                ...form.settings,
                                                externalLink:{
                                                    ...form.settings.externalLink,
                                                    link: e.target.value,
                                                }
                                            })
                                        }}*/
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
                                        /*defaultValue={form.settings.externalLink?.label}
                                        onChange={(e) => {
                                            setSettingsData({
                                                ...form.settings,
                                                externalLink:{
                                                    ...form.settings.externalLink,
                                                    label: e.target.value,
                                                }
                                            })
                                        }}*/
                                        placeholder={__('Link Label', 'smartpay')}
                                    />
                                </div>
                            </div>
                            {/*<p className="text-muted">* {__('It will show on payment receipt page.', 'smartpay')}</p>*/}
                        </div>
                </div>
            </Card.Body>
        </Card>
    )
}