import {
    PanelBody,
    TextControl,
    SelectControl,
    Button,
    Flex,
    FlexItem,
} from '@wordpress/components'
import { Icon, handle, closeSmall, plus } from '@wordpress/icons'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { useEffect } from '@wordpress/element'

export const edit = ({ attributes, setAttributes }) => {
    const { fieldName, options, defaultValue } = attributes
    const blockProps = useBlockProps({ className: 'form-control' })

    useEffect(() => {
        if (!fieldName) {
            setAttributes({ fieldName: Math.random().toString(36).substr(2, 11) })
        }
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    const changeOption = (index, key, value) => {
        const next = options.map((o, i) => (i === index ? { ...o, [key]: value } : o))
        setAttributes({ options: next })
    }
    const removeOption = (index) => {
        if (options.length <= 1) return
        setAttributes({ options: options.filter((o, i) => i !== index) })
    }
    const addOption = () => {
        setAttributes({ options: [...options, { value: '', label: __('New Option', 'smartpay') }] })
    }

    return (
        <>
            <select {...blockProps} value={defaultValue} onChange={() => {}}>
                {options.map((option, index) => (
                    <option key={index} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            <InspectorControls>
                <PanelBody title={__('Options', 'smartpay')} initialOpen={true}>
                    {options.map((option, index) => (
                        <Flex key={index}>
                            <FlexItem>
                                <Icon icon={handle} />
                            </FlexItem>
                            <FlexItem>
                                <TextControl
                                    __nextHasNoMarginBottom
                                    placeholder={__('Label', 'smartpay')}
                                    value={option.label}
                                    onChange={(v) => changeOption(index, 'label', v)}
                                />
                            </FlexItem>
                            <FlexItem>
                                <TextControl
                                    __nextHasNoMarginBottom
                                    placeholder={__('Value', 'smartpay')}
                                    value={option.value}
                                    onChange={(v) => changeOption(index, 'value', v)}
                                />
                            </FlexItem>
                            <FlexItem>
                                <Button icon={closeSmall} onClick={() => removeOption(index)} />
                            </FlexItem>
                        </Flex>
                    ))}
                    <Button isSecondary className="mt-2" onClick={addOption}>
                        <Icon icon={plus} />
                        {__('Add new', 'smartpay')}
                    </Button>

                    <div className="mt-2">
                        <SelectControl
                            __nextHasNoMarginBottom
                            label={__('Default option', 'smartpay')}
                            value={defaultValue}
                            onChange={(v) => setAttributes({ defaultValue: v })}
                            options={options.map((o) => ({ value: o.value, label: o.label }))}
                        />
                    </div>
                </PanelBody>
            </InspectorControls>
        </>
    )
}
