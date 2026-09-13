import { __ } from '@wordpress/i18n'
import { useEffect, useRef } from '@wordpress/element'
import {
    InspectorControls,
    useBlockProps,
    useInnerBlocksProps,
} from '@wordpress/block-editor'
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components'
import { useDispatch } from '@wordpress/data'
import { createBlock } from '@wordpress/blocks'

const templateToBlocks = (template) =>
    template.map(([name, attrs, innerTemplate = []]) =>
        createBlock(
            name,
            attrs,
            innerTemplate.map(([innerName, innerAttrs]) => createBlock(innerName, innerAttrs))
        )
    )

export const edit = ({ attributes, setAttributes, clientId }) => {
    const { showFirstName, showMiddleName, showLastName, columns } = attributes

    const { replaceInnerBlocks } = useDispatch('core/block-editor')
    const isFirstMount = useRef(true)

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

    // Sync inner blocks with attribute toggles. Skip mount — post content already
    // has the saved blocks; only re-sync when the user changes a toggle.
    useEffect(() => {
        if (isFirstMount.current) {
            isFirstMount.current = false
            return
        }
        replaceInnerBlocks(clientId, templateToBlocks(TEMPLATE), false)
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [showFirstName, showMiddleName, showLastName])

    const colClass = columns > 0 ? `sp-cols-${columns}` : ''
    const blockProps = useBlockProps({
        // "row" (flex) conflicts with sp-cols-N (grid) in the editor — drop it when a
        // column layout is active. save.js still emits "form-element row sp-cols-N"
        // for the frontend where Bootstrap order makes sp-cols-N win correctly.
        className: colClass ? `form-element ${colClass}` : 'form-element row',
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
