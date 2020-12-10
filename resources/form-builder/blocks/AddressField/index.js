import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const AddressField = {
    namespace: 'smartpay-form/address-input',
    settings: {
        title: __('Address Fields', 'smartpay'),
        description: __('Address fields', 'smartpay'),
        icon: page,
        keywords: ['input', 'address'],
        attributes: {
            attributes: {
                type: Object,
                default: {
                    name: '',
                    class: '',
                },
            },
            settings: {
                type: Object,
                default: {
                    visible: true,
                    labelPosition: 'top',
                },
            },
            validationRules: {
                type: Array,
                default: [],
            },
            fields: {
                type: Array,
                default: [
                    {
                        attributes: {
                            name: 'line_1',
                            value: '',
                            class: '',
                            placeholder: __('Address Line 1', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('Address Line 1', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                    {
                        attributes: {
                            type: 'text',
                            name: 'line_2',
                            value: '',
                            class: '',
                            placeholder: __('Address Line 2', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('Address Line 2', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                    {
                        attributes: {
                            type: 'text',
                            name: 'city',
                            value: '',
                            class: '',
                            placeholder: __('City', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('City', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                    {
                        attributes: {
                            type: 'text',
                            name: 'state',
                            value: '',
                            class: '',
                            placeholder: __('State', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('State', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                    {
                        attributes: {
                            type: 'text',
                            name: 'zip',
                            value: '',
                            class: '',
                            placeholder: __('Zip Code', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('Zip Code', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                    {
                        attributes: {
                            type: 'text',
                            name: 'country',
                            value: '',
                            class: '',
                            placeholder: __('Country', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('Country', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                ],
            },
        },
        edit,
        save,
    },
}
