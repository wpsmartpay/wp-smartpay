import {
    __experimentalInputControl as InputControl,
    TextControl,
    CheckboxControl,
    Button,
    Flex,
    FlexBlock,
    FlexItem,
    PanelBody,
    ToggleControl,
} from '@wordpress/components'

import { InspectorControls } from '@wordpress/block-editor'
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

export const edit = ({ attributes, setAttributes }) => {
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
                <Flex>
                    {attributes.fields.map((field, index) => {
                        return (
                            !!field.settings.visible && (
                                <FlexBlock key={index}>
                                    <TextControl
                                        type="text"
                                        label={field.settings.label}
                                        placeholder={
                                            field.attributes.placeholder
                                        }
                                        value=""
                                        onChange={() => {}}
                                    />
                                </FlexBlock>
                            )
                        )
                    })}
                </Flex>
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
                                                disabled={
                                                    'first_name' ===
                                                    field.attributes.name
                                                }
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
