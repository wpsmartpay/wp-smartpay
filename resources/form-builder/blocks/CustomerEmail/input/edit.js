import {
    PanelBody,
    TextControl,
    ToggleControl,
} from '@wordpress/components'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'

export const edit = ({ attributes, setAttributes }) => {
    const { placeholder, isRequired } = attributes
    const blockProps = useBlockProps({ className: 'form-control' })

    return (
        <>
            <input {...blockProps} type="email" placeholder={placeholder} readOnly />

            <InspectorControls>
                <PanelBody title={__('Input', 'smartpay')} initialOpen={true}>
                    <TextControl
                        __nextHasNoMarginBottom
                        label={__('Placeholder', 'smartpay')}
                        value={placeholder}
                        onChange={(v) => setAttributes({ placeholder: v })}
                    />
                    <ToggleControl
                        __nextHasNoMarginBottom
                        label={__('Required', 'smartpay')}
                        checked={isRequired}
                        onChange={(v) => setAttributes({ isRequired: v })}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    )
}
