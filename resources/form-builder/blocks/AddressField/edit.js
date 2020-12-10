import {
    __experimentalInputControl as InputControl,
    PanelBody,
    Flex,
    FlexBlock,
    FlexItem,
    TextControl,
    CheckboxControl,
    Button,
    ToggleControl,
} from '@wordpress/components'

import { InspectorControls } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { useEffect, useState } from '@wordpress/element'

const chunk = (arr, size) => {
    return Array.from({ length: Math.ceil(arr.length / size) }, (v, i) =>
        arr.slice(i * size, i * size + size)
    )
}

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

    const setFieldsItem = (field) => {
        setAttributes({
            fields: [
                ...attributes.fields.map((f) => {
                    return f.attributes.name === field.attributes.name
                        ? field
                        : f
                }),
            ],
        })
    }

    const toggleFieldValidationRule = (field, isRequired) => {
        let validationRules = [...field.validationRules]

        if (isRequired) {
            validationRules.push({
                required: {
                    value: true,
                    message: __('This field is required', 'smartpay'),
                },
            })
        } else {
            validationRules = field.validationRules.filter((rule) => {
                return 'required' !== Object.keys(rule)[0]
            })
        }

        setFieldsItem({
            ...field,
            attributes: {
                ...field.attributes,
                isRequired: isRequired,
            },
            validationRules,
        })
    }

    return (
        <>
            <div className="form-element">
                {chunk(attributes.fields, 2).map((items, index) => {
                    return (
                        <Flex key={index}>
                            {items.map((item, i) => {
                                return (
                                    !!item.settings.visible && (
                                        <FlexBlock key={i}>
                                            <TextControl
                                                type="text"
                                                label={item.settings.label}
                                                placeholder={
                                                    item.attributes.placeholder
                                                }
                                                value=""
                                                onChange={() => {}}
                                            />
                                        </FlexBlock>
                                    )
                                )
                            })}
                        </Flex>
                    )
                })}
            </div>

            <InspectorControls>
                <PanelBody
                    title={__('Settings', 'smartpay')}
                    initialOpen={true}
                >
                    {attributes.fields.map((field, index) => {
                        return (
                            <div key={index}>
                                <Accordion
                                    header={
                                        <>
                                            <CheckboxControl
                                                label={__(
                                                    field.settings.label,
                                                    'smartpay'
                                                )}
                                                checked={field.settings.visible}
                                                onChange={(value) => {
                                                    setFieldsItem({
                                                        ...field,
                                                        settings: {
                                                            ...field.settings,
                                                            visible: value,
                                                        },
                                                    })
                                                }}
                                            />
                                        </>
                                    }
                                    body={
                                        <>
                                            <FieldSettings
                                                field={field}
                                                setFieldsItem={setFieldsItem}
                                                toggleFieldValidationRule={
                                                    toggleFieldValidationRule
                                                }
                                            />
                                        </>
                                    }
                                ></Accordion>
                            </div>
                        )
                    })}
                </PanelBody>
            </InspectorControls>
        </>
    )
}

const Accordion = ({ header, body, opened = false }) => {
    const [isOpen, toggleOpen] = useState(opened)
    return (
        <div className="mt-3 accordion">
            <Flex>
                <FlexItem>{header}</FlexItem>
                <FlexItem>
                    <Button
                        icon={isOpen ? 'arrow-up-alt2' : 'arrow-down-alt2'}
                        label="More"
                        isSmall
                        onClick={() => {
                            toggleOpen(!isOpen)
                        }}
                    />
                </FlexItem>
            </Flex>

            {isOpen && <div className="bg-light p-3">{body}</div>}
        </div>
    )
}

const FieldSettings = ({ field, setFieldsItem, toggleFieldValidationRule }) => {
    return (
        <div>
            <InputControl
                type="text"
                label={__('Label', 'smartpay')}
                value={field.settings.label}
                onChange={(value) => {
                    setFieldsItem({
                        ...field,
                        settings: { ...field.settings, label: value },
                    })
                }}
            />
            <InputControl
                type="text"
                label={__('Placeholder', 'smartpay')}
                value={field.attributes.placeholder}
                className="mt-3"
                onChange={(value) => {
                    setFieldsItem({
                        ...field,
                        attributes: { ...field.attributes, placeholder: value },
                    })
                }}
            />

            <ToggleControl
                label={__('Is required', 'smartpay')}
                checked={field.attributes.isRequired}
                value={true}
                className="mt-3"
                onChange={(value) => {
                    toggleFieldValidationRule(field, value)
                }}
            />
        </div>
    )
}
