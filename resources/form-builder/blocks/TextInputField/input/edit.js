import {
    PanelBody,
    TextControl,
    SelectControl,
    ToggleControl,
} from '@wordpress/components'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { useEffect } from '@wordpress/element'

const TYPES = [
    { label: __('Text', 'smartpay'), value: 'text' },
    { label: __('Number', 'smartpay'), value: 'number' },
    { label: __('Email', 'smartpay'), value: 'email' },
]

export const edit = ({ attributes, setAttributes }) => {
    const { fieldName, inputType, placeholder, isRequired } = attributes
    const blockProps = useBlockProps({ className: 'form-control' })

    // Give the field a stable, unique submission name on first insert.
    useEffect(() => {
        if (!fieldName) {
            setAttributes({ fieldName: Math.random().toString(36).substr(2, 11) })
        }
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <>
            <input
                {...blockProps}
                type={inputType === 'number' ? 'number' : 'text'}
                placeholder={placeholder}
                readOnly
            />

            <InspectorControls>
                <PanelBody title={__('Input', 'smartpay')} initialOpen={true}>
                    <SelectControl
                        __nextHasNoMarginBottom
                        label={__('Type', 'smartpay')}
                        value={inputType}
                        options={TYPES}
                        onChange={(v) => setAttributes({ inputType: v })}
                    />
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
