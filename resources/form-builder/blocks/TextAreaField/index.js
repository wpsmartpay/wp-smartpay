import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const TextAreaField = {
    namespace: 'smartpay-form/textare-input',
    settings: {
        title: __('Text Area Fields', 'smartpay'),
        description: __('Text Area fields', 'smartpay'),
        icon: page,
        keywords: ['input', 'text', 'textarea'],
        attributes: {
            attributes: {
                type: Object,
                default: {
                    name: '',
                    value: '',
                    class: '',
                    placeholder: '',
                    rows: 3,
                    isRequired: false,
                },
            },
            settings: {
                type: Object,
                default: {
                    visible: true,
                    label: 'Text Area',
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
