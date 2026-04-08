import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { useEffect, useState } from '@wordpress/element'
import { Placeholder, SelectControl, Spinner } from '@wordpress/components'
import { InspectorControls } from '@wordpress/block-editor'
import { PanelBody, SelectControl as SidebarSelect, TextControl } from '@wordpress/components'
import apiFetch from '@wordpress/api-fetch'

export default registerBlockType('smartpay/product', {
    title: __('SmartPay Product', 'smartpay'),
    description: __('Display a SmartPay product with popup or embedded checkout.', 'smartpay'),
    icon: 'cart',
    category: 'widgets',
    keywords: [__('payment', 'smartpay'), __('product', 'smartpay'), __('checkout', 'smartpay')],

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
        const [products, setProducts] = useState([])
        const [isLoading, setIsLoading] = useState(true)

        useEffect(() => {
            apiFetch({
                path: 'smartpay/v1/products',
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
                .then((data) => {
                    const productList = (data?.products || []).map((product) => ({
                        value: product.id,
                        label: `(#${product.id}) ${product.title}`,
                    }))
                    setProducts(productList)
                    setIsLoading(false)
                })
                .catch(() => {
                    setProducts([])
                    setIsLoading(false)
                })
        }, [])

        const productOptions = [
            { value: 0, label: __('Select a product', 'smartpay') },
            ...products,
        ]

        const selectedProduct = products.find((p) => p.value === attributes.id)

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Product Settings', 'smartpay')}>
                        <SelectControl
                            label={__('Shortcode behavior', 'smartpay')}
                            value={attributes.behavior}
                            onChange={(value) => setAttributes({ behavior: value })}
                            options={[
                                { value: 'popup', label: __('Popup', 'smartpay') },
                                { value: 'embedded', label: __('Embedded', 'smartpay') },
                            ]}
                            __nextHasNoMarginBottom
                        />
                        {attributes.behavior === 'popup' && (
                            <TextControl
                                label={__('Button label', 'smartpay')}
                                value={attributes.label}
                                onChange={(value) => setAttributes({ label: value })}
                                __nextHasNoMarginBottom
                            />
                        )}
                    </PanelBody>
                </InspectorControls>

                <Placeholder
                    icon="cart"
                    label={__('SmartPay Product', 'smartpay')}
                    instructions={
                        selectedProduct
                            ? __('Selected: ', 'smartpay') + selectedProduct.label
                            : __('Choose a product to display on this page.', 'smartpay')
                    }
                >
                    {isLoading ? (
                        <Spinner />
                    ) : (
                        <SelectControl
                            value={attributes.id}
                            onChange={(value) => setAttributes({ id: parseInt(value) })}
                            options={productOptions}
                            __nextHasNoMarginBottom
                        />
                    )}
                </Placeholder>
            </>
        )
    },

    save: ({ attributes }) => {
        return `[smartpay_product id="${attributes.id}" behavior="${attributes.behavior}" label="${attributes.label}"]`
    },
})
