import { TextControl, PanelBody } from '@wordpress/components'

import { InspectorControls } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'

export const edit = ({ attributes, setAttributes }) => {
    const setSettingsData = (data) => {
        setAttributes({
            settings: {
                ...attributes.settings,
                ...data,
            },
        })
    }

    const setAttributesData = (data) => {
        setAttributes({
            attributes: {
                ...attributes.attributes,
                ...data,
            },
        })
    }

    return (
        <>
            <div className="form-element">
                <TextControl
                    type="text"
                    label={attributes.settings.label}
                    placeholder={attributes.attributes.placeholder}
                />
            </div>

            <InspectorControls>
                <PanelBody
                    title={__('Settings', 'smartpay')}
                    initialOpen={true}
                >
                    <TextControl
                        type="text"
                        label={__('Label', 'smartpay')}
                        value={attributes.settings.label}
                        onChange={(value) => {
                            setSettingsData({
                                label: value,
                            })
                        }}
                    />
                    <TextControl
                        type="text"
                        label={__('Placeholder', 'smartpay')}
                        value={attributes.attributes.placeholder}
                        className="mt-3"
                        onChange={(value) => {
                            setAttributesData({
                                placeholder: value,
                            })
                        }}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    )
}
