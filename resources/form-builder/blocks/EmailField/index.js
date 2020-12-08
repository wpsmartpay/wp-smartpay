import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const EmailField = {
    namespace: 'smartpay-form/email-input',
    settings: {
        title: __('Email Input Field', 'smartpay'),
        description: __('Email Input field', 'smartpay'),
        icon: page,
        keywords: ['input', 'email'],
        attributes: {
            attributes: {
                type: Object,
                default: {
                    name: '',
                    value: '',
                    class: '',
                    placeholder: '',
                    isRequired: false,
                },
            },
            settings: {
                type: Object,
                default: {
                    visible: true,
                    label: 'Email Input',
                    helpMessage: '',
                    labelPosition: 'top',
                },
            },
            validationRules: {
                type: Array,
                default: [],
            },
        },
        edit,
        save,
    },
}
