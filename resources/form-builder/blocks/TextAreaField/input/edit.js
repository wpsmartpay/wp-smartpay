import {
    PanelBody,
    TextControl,
    RangeControl,
    ToggleControl,
} from '@wordpress/components'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { useEffect } from '@wordpress/element'

export const edit = ({ attributes, setAttributes }) => {
    const { fieldName, placeholder, isRequired, rows } = attributes
    const blockProps = useBlockProps({ className: 'form-control' })

    useEffect(() => {
        if (!fieldName) {
            setAttributes({ fieldName: Math.random().toString(36).substr(2, 11) })
        }
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <>
            <textarea {...blockProps} placeholder={placeholder} rows={rows} readOnly />

            <InspectorControls>
                <PanelBody title={__('Input', 'smartpay')} initialOpen={true}>
                    <TextControl
                        __nextHasNoMarginBottom
                        label={__('Placeholder', 'smartpay')}
                        value={placeholder}
                        onChange={(v) => setAttributes({ placeholder: v })}
                    />
                    <RangeControl
                        __nextHasNoMarginBottom
                        label={__('Rows', 'smartpay')}
                        value={rows}
                        onChange={(v) => setAttributes({ rows: v })}
                        min={2}
                        max={12}
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
