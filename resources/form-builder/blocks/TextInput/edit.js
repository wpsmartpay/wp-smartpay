import {
    __experimentalInputControl as InputControl,
    PanelBody,
    ToggleControl,
} from '@wordpress/components'

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

    const setValidationRulesData = (validationRules) => {
        setAttributes({ validationRules })
    }

    const toggleValidationRule = (isRequired) => {
        setAttributesData({ isRequired })

        let validationRules = []

        if (isRequired) {
            validationRules = [
                ...attributes.validationRules,
                {
                    required: {
                        value: true,
                        message: __('This field is required', 'smartpay'),
                    },
                },
            ]
        } else {
            validationRules = attributes.validationRules.filter((rule) => {
                return 'required' !== Object.keys(rule)[0]
            })
        }

        setValidationRulesData(validationRules)
    }

    return (
        <>
            <div className="form-element">
                <InputControl
                    label={attributes.settings.label}
                    placeholder={attributes.attributes.placeholder}
                />
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
                    <InputControl
                        type="text"
                        label={__('Placeholder', 'smartpay')}
                        value={attributes.attributes.placeholder}
                        className="mt-3"
                        onChange={(value) => {
                            setAttributesData({
                                placeholder: value,
                            })
                        }}
                    />

                    <ToggleControl
                        label={__('Is required', 'smartpay')}
                        checked={attributes.attributes.isRequired}
                        value={true}
                        className="mt-3"
                        onChange={(value) => {
                            toggleValidationRule(value)
                        }}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    )
}
