import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const NameField = {
    namespace: 'smartpay-form/name',
    settings: {
        title: __('Name Fields', 'smartpay'),
        description: __('Name fields', 'smartpay'),
        icon: page,
        keywords: ['name', 'first name', 'last name'],
        attributes: {
            attributes: {
                type: Object,
                default: {
                    name: 'name',
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
                            name: 'first_name',
                            value: '',
                            class: '',
                            placeholder: __('First Name', 'smartpay'),
                            isRequired: true,
                        },
                        settings: {
                            visible: true,
                            label: __('First Name', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [
                            {
                                required: {
                                    value: true,
                                    message: __(
                                        'This field is required',
                                        'smartpay'
                                    ),
                                },
                            },
                        ],
                    },
                    {
                        attributes: {
                            name: 'middle_name',
                            value: '',
                            class: '',
                            placeholder: __('Middle Name', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: false,
                            label: __('Middle Name', 'smartpay'),
                            helpMessage: '',
                        },
                        validationRules: [],
                    },
                    {
                        attributes: {
                            name: 'last_name',
                            value: '',
                            class: '',
                            placeholder: __('Last Name', 'smartpay'),
                            isRequired: false,
                        },
                        settings: {
                            visible: true,
                            label: __('Last Name', 'smartpay'),
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
