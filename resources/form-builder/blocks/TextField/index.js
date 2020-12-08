import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const TextField = {
    namespace: 'smartpay-form/text-input',
    settings: {
        title: __('Text Input Fields', 'smartpay'),
        description: __('Text Input fields', 'smartpay'),
        icon: page,
        keywords: ['input', 'text'],
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
                    label: 'Text Input',
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
