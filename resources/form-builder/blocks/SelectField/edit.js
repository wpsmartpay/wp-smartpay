import {
    __experimentalInputControl as InputControl,
    PanelBody,
    RadioControl,
    Flex,
    FlexItem,
    FlexBlock,
    TextControl,
    Button,
    SelectControl,
} from '@wordpress/components'
import { Icon, handle, closeSmall, plus } from '@wordpress/icons'
import { InspectorControls } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { useEffect } from '@wordpress/element'

export const edit = ({ attributes, setAttributes }) => {
    useEffect(() => {
        if (attributes.attributes.name) {
            return
        }

        setAttributesData({
            name: Math.random().toString(36).substr(2, 11),
        })
    }, [])

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

    const changeOptionLabel = (label, index) => {
        const options = [...attributes.attributes.options]
        options[index]['label'] = label
        setAttributesData({ options })
    }

    const changeOptionValue = (label, index) => {
        const options = [...attributes.attributes.options]
        options[index]['value'] = label
        setAttributesData({ options })
    }

    const removeOption = (index) => {
        if (attributes.attributes.options.length <= 1) {
            return
        }

        const options = [...attributes.attributes.options].filter(
            (option, i) => {
                return index != i
            }
        )
        setAttributesData({ options })
    }

    const addNewOption = () => {
        setAttributesData({
            options: [
                ...attributes.attributes.options,
                { value: 0, label: __('New Option', 'smartpay') },
            ],
        })
    }

    const setDefaultOption = (value) => {
        setAttributesData({ defaultValue: value })
    }

    return (
        <>
            <div className="form-element">
                <Flex>
                    <FlexItem style={{ width: '50%' }}>
                        <SelectControl
                            label={attributes.settings.label}
                            value={attributes.attributes.defaultValue}
                            options={attributes.attributes.options.map(
                                (option) => {
                                    return {
                                        label: option.label,
                                        value: option.value,
                                    }
                                }
                            )}
                            onChange={setDefaultOption}
                        />
                    </FlexItem>
                </Flex>
            </div>

            <InspectorControls>
                <PanelBody
                    title={__('Settings', 'smartpay')}
                    initialOpen={true}
                >
                    <InputControl
                        type="text"
                        label={__('Label', 'smartpay')}
                        value={attributes.settings.label}
                        onChange={(value) => {
                            setSettingsData({
                                label: value,
                            })
                        }}
                    />
                </PanelBody>

                <PanelBody title={__('Radio Options', 'smartpay')}>
                    {attributes.attributes.options.map((option, index) => {
                        return (
                            <Flex key={index}>
                                <FlexItem>
                                    <Icon icon={handle} />
                                </FlexItem>
                                <FlexItem>
                                    <TextControl
                                        type="text"
                                        placeholder={__('Label', 'smartpay')}
                                        value={option.label}
                                        onChange={(label) =>
                                            changeOptionLabel(label, index)
                                        }
                                    />
                                </FlexItem>
                                <FlexItem>
                                    <TextControl
                                        placeholder={__('Value', 'smartpay')}
                                        value={option.value}
                                        onChange={(value) =>
                                            changeOptionValue(value, index)
                                        }
                                    />
                                </FlexItem>
                                <FlexItem>
                                    <Button
                                        icon={closeSmall}
                                        onClick={() => removeOption(index)}
                                    />
                                </FlexItem>
                            </Flex>
                        )
                    })}
                    <Button isSecondary className="mt-2" onClick={addNewOption}>
                        <Icon icon={plus} />
                        {__('Add new', 'smartpay')}
                    </Button>

                    <div className="mt-2">
                        <SelectControl
                            label={__('Default option', 'smartpay')}
                            value={attributes.attributes.defaultValue}
                            onChange={setDefaultOption}
                            options={attributes.attributes.options.map(
                                (option) => {
                                    return {
                                        value: option.value,
                                        label: option.label,
                                    }
                                }
                            )}
                        />
                    </div>
                </PanelBody>
            </InspectorControls>
        </>
    )
}
