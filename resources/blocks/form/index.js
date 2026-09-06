import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { useEffect, useState } from '@wordpress/element'
import { Placeholder, SelectControl, Spinner, Notice } from '@wordpress/components'
import { useBlockProps } from '@wordpress/block-editor'
import { useSelect } from '@wordpress/data'
import apiFetch from '@wordpress/api-fetch'

export default registerBlockType('smartpay/form', {
    apiVersion: 3,
    title: __('WPSmartPay Form', 'smartpay'),
    description: __('Display a WPSmartPay payment form embedded on the page.', 'smartpay'),
    icon: 'feedback',
    category: 'widgets',
    keywords: [__('payment', 'smartpay'), __('form', 'smartpay'), __('checkout', 'smartpay'), __('wpsmartpay', 'smartpay')],

    attributes: {
        id: {
            type: 'integer',
            default: 0,
        },
    },

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps()
        const postType = useSelect(
            (select) => select('core/editor').getCurrentPostType(),
            []
        )

        const [forms, setForms] = useState([])
        const [isLoading, setIsLoading] = useState(true)

        useEffect(() => {
            const url = new URL(`${smartpay.restUrl}/v1/native-forms`)
            url.searchParams.set('per_page', '100')
            apiFetch({
                url: url.toString(),
                headers: {
                    'X-WP-Nonce': smartpay.apiNonce,
                },
            })
                .then((data) => {
                    const formList = (data?.forms?.data || []).map((form) => ({
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

        if (postType === 'smartpay_form') {
            return (
                <div {...blockProps}>
                    <Notice status="warning" isDismissible={false}>
                        {__('SmartPay blocks cannot be embedded inside a SmartPay form.', 'smartpay')}
                    </Notice>
                </div>
            )
        }

        const formOptions = [
            { value: 0, label: __('Select a form', 'smartpay') },
            ...forms,
        ]

        const selectedForm = forms.find((f) => f.value === attributes.id)

        return (
            <div {...blockProps}>
                <Placeholder
                    icon="feedback"
                    label={__('WPSmartPay Form', 'smartpay')}
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
            </div>
        )
    },

    // Dynamic block — rendered server-side via render_callback in Admin.php
    save: () => null,
})
