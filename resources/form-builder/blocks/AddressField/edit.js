import { __ } from '@wordpress/i18n'
import {
    InspectorControls,
    useBlockProps,
    useInnerBlocksProps,
} from '@wordpress/block-editor'
import { PanelBody, ToggleControl } from '@wordpress/components'

const line = (label, fieldName, fieldType) => [
    'smartpay-form/address-field',
    { label, fieldType },
    [
        ['smartpay-form/address-label', { text: label, htmlFor: fieldName }],
        ['smartpay-form/address-input-field', { fieldName, placeholder: label, fieldType }],
    ],
]

export const edit = ({ attributes, setAttributes }) => {
    const {
        showLine1,
        showLine2,
        showCity,
        showState,
        showZip,
        showCountry,
    } = attributes

    // Country first so the State options can depend on it (frontend cascade).
    const TEMPLATE = []
    if (showLine1)   TEMPLATE.push(line(__('Address Line 1', 'smartpay'), 'line_1', 'text'))
    if (showLine2)   TEMPLATE.push(line(__('Address Line 2', 'smartpay'), 'line_2', 'text'))
    if (showCity)    TEMPLATE.push(line(__('City', 'smartpay'),           'city',   'text'))
    if (showCountry) TEMPLATE.push(line(__('Country', 'smartpay'),        'country','country'))
    if (showState)   TEMPLATE.push(line(__('State', 'smartpay'),          'state',  'state'))
    if (showZip)     TEMPLATE.push(line(__('Zip Code', 'smartpay'),       'zip',    'text'))

    const blockProps = useBlockProps({ className: 'smartpay-address' })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ['smartpay-form/address-field'],
        templateLock: 'all',
    })

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Address Settings', 'smartpay')}>
                    <ToggleControl
                        label={__('Show Address Line 1', 'smartpay')}
                        checked={showLine1}
                        onChange={(val) => setAttributes({ showLine1: val })}
                    />
                    <ToggleControl
                        label={__('Show Address Line 2', 'smartpay')}
                        checked={showLine2}
                        onChange={(val) => setAttributes({ showLine2: val })}
                    />
                    <ToggleControl
                        label={__('Show City', 'smartpay')}
                        checked={showCity}
                        onChange={(val) => setAttributes({ showCity: val })}
                    />
                    <ToggleControl
                        label={__('Show State', 'smartpay')}
                        checked={showState}
                        onChange={(val) => setAttributes({ showState: val })}
                    />
                    <ToggleControl
                        label={__('Show Zip Code', 'smartpay')}
                        checked={showZip}
                        onChange={(val) => setAttributes({ showZip: val })}
                    />
                    <ToggleControl
                        label={__('Show Country', 'smartpay')}
                        checked={showCountry}
                        onChange={(val) => setAttributes({ showCountry: val })}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...innerBlocksProps} />
        </>
    )
}
