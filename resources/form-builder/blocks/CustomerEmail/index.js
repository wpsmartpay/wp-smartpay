import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const CustomerEmail = {
    namespace: 'smartpay-form/email',
    settings: {
        title: __('Email Fields', 'smartpay'),
        description: __('Email fields', 'smartpay'),
        icon: page,
        keywords: ['email'],
        attributes: {
            attributes: {
                type: Object,
                default: {
                    name: 'email',
                    value: '',
                    class: '',
                    placeholder: 'Email',
                    isRequired: true,
                },
            },
            settings: {
                type: Object,
                default: {
                    visible: true,
                    label: 'Email',
                    helpMessage: '',
                    labelPosition: 'top',
                },
            },
            validationRules: {
                type: Array,
                default: [
                    {
                        required: {
                            value: true,
                            message: __('This field is required', 'smartpay'),
                        },
                    },
                ],
            },
        },
        edit,
        save,
    },
}
