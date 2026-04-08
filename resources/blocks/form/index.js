import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { useEffect, useState } from '@wordpress/element'
import { Placeholder, SelectControl, Spinner, PanelBody, TextControl } from '@wordpress/components'
import { InspectorControls } from '@wordpress/block-editor'
import apiFetch from '@wordpress/api-fetch'

export default registerBlockType('smartpay/form', {
    title: __('SmartPay Form', 'smartpay'),
    description: __('Display a SmartPay payment form with popup or embedded checkout.', 'smartpay'),
    icon: 'feedback',
    category: 'widgets',
    keywords: [__('payment', 'smartpay'), __('form', 'smartpay'), __('checkout', 'smartpay')],

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
        const [forms, setForms] = useState([])
        const [isLoading, setIsLoading] = useState(true)

        useEffect(() => {
            apiFetch({
                path: 'smartpay/v1/forms',
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
                .then((data) => {
                    const formList = (data?.forms || []).map((form) => ({
                        value: form.id,
                        label: `(#${form.id}) ${form.title}`,
                    }))
                    setForms(formList)
                    setIsLoading(false)
                })
                .catch(() => {
                    setForms([])
                    setIsLoading(false)
                })
        }, [])

        const formOptions = [
            { value: 0, label: __('Select a form', 'smartpay') },
            ...forms,
        ]

        const selectedForm = forms.find((f) => f.value === attributes.id)

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Form Settings', 'smartpay')}>
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
                    icon="feedback"
                    label={__('SmartPay Form', 'smartpay')}
                    instructions={
                        selectedForm
                            ? __('Selected: ', 'smartpay') + selectedForm.label
                            : __('Choose a form to display on this page.', 'smartpay')
                    }
                >
                    {isLoading ? (
                        <Spinner />
                    ) : (
                        <SelectControl
                            value={attributes.id}
                            onChange={(value) => setAttributes({ id: parseInt(value) })}
                            options={formOptions}
                            __nextHasNoMarginBottom
                        />
                    )}
                </Placeholder>
            </>
        )
    },

    save: ({ attributes }) => {
        return `[smartpay_form id="${attributes.id}" behavior="${attributes.behavior}" label="${attributes.label}"]`
    },
})
