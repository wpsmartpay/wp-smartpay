import {Card, Form} from "react-bootstrap";
import {__} from "@wordpress/i18n";

export const OptionComponent = ({product, setProductData}) => {
    const _setSettingsData = (settings) => {
        setProductData({
            ...product,
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
                                            {__('Checkout Label', 'smartpay')}
                                        </label>
                                        <Form.Control
                                            className="mt-2"
                                            type="text"
                                            size="sm"
                                            value={product?.settings?.payButtonLabel}
                                            placeholder={__(
                                                'ex. Get it now',
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
                                </div>
                            </Card>

                            <Card className="my-3 bg-light">
                                <div className="p-3">
                                    <div className="custom-control custom-checkbox py-1">
                                        <input
                                            type="checkbox"
                                            className="custom-control-input"
                                            id="allowExternalResourceLinkOnPaymentSuccessPage"
                                            value="true"
                                            checked={product.settings?.externalLink?.allowExternalLink}
                                            onChange={(e) => {
                                                _setSettingsData({
                                                    ...product?.settings,
                                                    externalLink:{
                                                        ...product.settings?.externalLink,
                                                        allowExternalLink: e.target.checked,
                                                    },
                                                })
                                            }}
                                        />
                                        <label
                                            className="custom-control-label pt-1"
                                            htmlFor="allowExternalResourceLinkOnPaymentSuccessPage"
                                        >
                                            {__('Add resource link on Payment Success Page', 'smartpay')}
                                        </label>
                                    </div>
                                    {product.settings?.externalLink?.allowExternalLink &&
                                        <div className="form-group mt-4 mb-0">
                                            <div className="d-flex">
                                                <div className="w-75 mr-4">
                                                    <label>
                                                        {__('External Resource Link', 'smartpay')}
                                                    </label>
                                                    <Form.Control
                                                        className="mt-2"
                                                        type="text"
                                                        size="sm"
                                                        defaultValue={product.settings?.externalLink?.link}
                                                        onChange={(e) => {
                                                            _setSettingsData({
                                                                ...product.settings,
                                                                externalLink:{
                                                                    ...product.settings?.externalLink,
                                                                    link: e.target.value,
                                                                }
                                                            })
                                                        }}
                                                        placeholder={__('ex. https://resourcelink.com', 'smartpay')}
                                                    />
                                                </div>
                                                <div className="w-25">
                                                    <label>
                                                        {__('Link Label', 'smartpay')}
                                                    </label>
                                                    <Form.Control
                                                        className="mt-2"
                                                        type="text"
                                                        defaultValue={product.settings?.externalLink?.label}
                                                        onChange={(e) => {
                                                            _setSettingsData({
                                                                ...product?.settings,
                                                                externalLink:{
                                                                    ...product?.settings?.externalLink,
                                                                    label: e.target.value,
                                                                }
                                                            })
                                                        }}
                                                        placeholder={__('ex. Show link', 'smartpay')}
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