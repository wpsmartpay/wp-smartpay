import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { useEffect, useState } from '@wordpress/element'
import { Placeholder, SelectControl, Spinner } from '@wordpress/components'
import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
import { PanelBody } from '@wordpress/components'
import ServerSideRender from '@wordpress/server-side-render'
import apiFetch from '@wordpress/api-fetch'

export default registerBlockType('smartpay/campaign-progress', {
    apiVersion: 3,
    title: __('WPSmartPay Campaign Progress', 'smartpay'),
    description: __("Show a campaign's goal progress bar on any page or post.", 'smartpay'),
    icon: 'chart-bar',
    category: 'widgets',

    attributes: {
        id: { type: 'integer', default: 0 },
    },

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps()
        const [campaigns, setCampaigns] = useState(null)

        useEffect(() => {
            const url = new URL(`${smartpay.restUrl}/v1/campaigns`)
            url.searchParams.set('per_page', '100')
            apiFetch({ url: url.toString(), headers: { 'X-WP-Nonce': smartpay.apiNonce } })
                .then((data) => setCampaigns((data?.campaigns?.data || []).map((c) => ({ value: c.id, label: `(#${c.id}) ${c.title}` }))))
                .catch(() => setCampaigns([]))
        }, [])

        const picker = (
            <SelectControl
                __nextHasNoMarginBottom
                label={__('Campaign', 'smartpay')}
                value={attributes.id}
                options={[{ value: 0, label: __('Select a campaign', 'smartpay') }, ...(campaigns || [])]}
                onChange={(id) => setAttributes({ id: parseInt(id, 10) || 0 })}
            />
        )

        if (!attributes.id) {
            return (
                <div {...blockProps}>
                    <Placeholder icon="chart-bar" label={__('Campaign Progress', 'smartpay')}>
                        {campaigns === null ? <Spinner /> : picker}
                    </Placeholder>
                </div>
            )
        }

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Campaign', 'smartpay')}>{campaigns === null ? <Spinner /> : picker}</PanelBody>
                </InspectorControls>
                <ServerSideRender block="smartpay/campaign-progress" attributes={attributes} />
            </div>
        )
    },

    save: () => null,
})
