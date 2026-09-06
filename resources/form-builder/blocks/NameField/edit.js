import { __ } from '@wordpress/i18n'
import {
    InspectorControls,
    useBlockProps,
    useInnerBlocksProps,
} from '@wordpress/block-editor'
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components'

export const edit = ({ attributes, setAttributes }) => {
    const { showFirstName, showMiddleName, showLastName, columns } = attributes

    const TEMPLATE = []

    if (showFirstName) {
        TEMPLATE.push([
            'smartpay-form/name-field',
            { label: __('First Name', 'smartpay'), fieldType: 'first_name' },
            [
                ['smartpay-form/name-label', { text: __('First Name', 'smartpay'), htmlFor: 'first_name' }],
                [
                    'smartpay-form/name-input',
                    {
                        fieldName: 'first_name',
                        placeholder: __('First Name', 'smartpay'),
                        isRequired: true,
                    },
                ],
            ],
        ])
    }

    if (showMiddleName) {
        TEMPLATE.push([
            'smartpay-form/name-field',
            { label: __('Middle Name', 'smartpay'), fieldType: 'middle_name' },
            [
                ['smartpay-form/name-label', { text: __('Middle Name', 'smartpay'), htmlFor: 'middle_name' }],
                [
                    'smartpay-form/name-input',
                    {
                        fieldName: 'middle_name',
                        placeholder: __('Middle Name', 'smartpay'),
                    },
                ],
            ],
        ])
    }

    if (showLastName) {
        TEMPLATE.push([
            'smartpay-form/name-field',
            { label: __('Last Name', 'smartpay'), fieldType: 'last_name' },
            [
                ['smartpay-form/name-label', { text: __('Last Name', 'smartpay'), htmlFor: 'last_name' }],
                [
                    'smartpay-form/name-input',
                    {
                        fieldName: 'last_name',
                        placeholder: __('Last Name', 'smartpay'),
                    },
                ],
            ],
        ])
    }

    const colClass = columns > 0 ? `sp-cols-${columns}` : ''
    const blockProps = useBlockProps({
        className: `form-element row${colClass ? ' ' + colClass : ''}`,
    })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        template: TEMPLATE,
        allowedBlocks: ['smartpay-form/name-field'],
        templateLock: 'all',
    })

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Name Settings', 'smartpay')}>
                    <ToggleControl
                        label={__('Show First Name', 'smartpay')}
                        checked={showFirstName}
                        onChange={(val) => setAttributes({ showFirstName: val })}
                    />
                    <ToggleControl
                        label={__('Show Middle Name', 'smartpay')}
                        checked={showMiddleName}
                        onChange={(val) => setAttributes({ showMiddleName: val })}
                    />
                    <ToggleControl
                        label={__('Show Last Name', 'smartpay')}
                        checked={showLastName}
                        onChange={(val) => setAttributes({ showLastName: val })}
                    />
                    <SelectControl
                        label={__('Layout Columns', 'smartpay')}
                        value={columns}
                        options={[
                            { label: __('Auto (flex row)', 'smartpay'), value: 0 },
                            { label: __('1 Column', 'smartpay'), value: 1 },
                            { label: __('2 Columns', 'smartpay'), value: 2 },
                            { label: __('3 Columns', 'smartpay'), value: 3 },
                        ]}
                        onChange={(val) =>
                            setAttributes({ columns: parseInt(val, 10) })
                        }
                        __nextHasNoMarginBottom
                    />
                </PanelBody>
            </InspectorControls>
            <div {...innerBlocksProps} />
        </>
    )
}
