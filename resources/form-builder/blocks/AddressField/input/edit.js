import { PanelBody, TextControl, ToggleControl } from '@wordpress/components'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { COUNTRIES, DEFAULT_STATES } from '../data/locations'

export const edit = ({ attributes, setAttributes }) => {
    const { fieldType, placeholder, isRequired } = attributes
    const blockProps = useBlockProps({ className: 'form-control' })
    const isSelect = fieldType === 'country' || fieldType === 'state'
    const options = fieldType === 'country' ? COUNTRIES : DEFAULT_STATES
    const prompt =
        placeholder ||
        (fieldType === 'country'
            ? __('Select country', 'smartpay')
            : __('Select state', 'smartpay'))

    return (
        <>
            {isSelect ? (
                <select {...blockProps} disabled>
                    <option value="">{prompt}</option>
                    {options.map((o) => (
                        <option key={o.code} value={o.code}>
                            {o.name}
                        </option>
                    ))}
                </select>
            ) : (
                <input {...blockProps} type="text" placeholder={placeholder} readOnly />
            )}

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
