import {Form} from "react-bootstrap";
import {__} from "@wordpress/i18n";

export const OptionComponent = ({product, setProductData}) => {
    const _setSettingsData = (settings) => {
        setProductData({
            ...product,
            settings,
        })
    }
    return (
                <div className="col-md-12 mx-auto">
                    <div className="form-group mb-0">
                        <label>
                            {__('Pay Button Label', 'smartpay')}
                        </label>
                        <Form.Control
                            className="mt-1"
                            type="text"
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
                    <div className="custom-control custom-checkbox py-1 mt-4">
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
                            {__('Add External Link on Success Page', 'smartpay')}
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
                                        type="text"
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
                                        placeholder={__('ex. https://example.com', 'smartpay')}
                                    />
                                </div>
                                <div className="w-25">
                                    <label>
                                        {__('Link Label', 'smartpay')}
                                    </label>
                                    <Form.Control
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
    )
}