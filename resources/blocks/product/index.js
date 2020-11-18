const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { Fragment } = wp.element

import Sidebar from './components/Sidebar'
import SelectProduct from './components/SelectProduct'

export default registerBlockType('smartpay/product', {
    title: __('SmartPay Product', 'smartpay'),
    description: __('Simple block to show a product', 'smartpay'),
    icon: 'format-aside',
    category: 'widgets',

    attributes: {
        id: {
            type: 'integer',
            default: 0,
        },
        behavior: {
            type: 'string',
            default: 'popup',
        },
        label: {
            type: 'string',
            default: '',
        },
    },

    edit: ({ attributes, setAttributes }) => {
        function setId(id) {
            setAttributes({ id: parseInt(id) })
        }

        function setBehavior(behavior) {
            setAttributes({ behavior: behavior })
        }

        function setLabel(label) {
            setAttributes({ label: label })
        }

        let productOptions = [
            {
                value: null,
                label: __('Select a product', 'smartpay'),
            },
            ...JSON.parse(smartpay_block_editor_products).map((product) => {
                return {
                    value: product.id,
                    label: `(#${product.id}) ${product.title}`,
                }
            }),
        ]

        return (
            <Fragment>
                <div className="smartpay">
                    <div className="container block-editor product card py-4">
                        <div className="card-body text-center">
                            {/* <img src={smartpay_logo} className="logo img-fluid" /> */}
                            <strong>{__('SmartPay', 'smartpay')}</strong>
                            <div className="d-flex justify-content-center mt-1">
                                <div className="col-md-8">
                                    <h5
                                        className="text-center mb-3 m-0 font-weight-normal"
                                        style={{ fontSize: '16px' }}
                                    >
                                        {__('Select a Product', 'smartpay')}
                                    </h5>
                                    <SelectProduct
                                        productOptions={productOptions}
                                        productId={attributes.id}
                                        onSetId={setId}
                                        className="form-control form-control-sm mx-auto"
                                    ></SelectProduct>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Sidebar
                    attributes={attributes}
                    onSetId={setId}
                    onSetBehavior={setBehavior}
                    onSetLabel={setLabel}
                ></Sidebar>
            </Fragment>
        )
    },

    save: ({ attributes }) => {
        return `[smartpay_product id="${attributes.id}" behavior="${attributes.behavior}" label="${attributes.label}"]`
    },
})
