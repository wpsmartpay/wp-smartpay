import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const RadioField = {
    namespace: 'smartpay-form/radio-input',
    settings: {
        title: __('Radio Fields', 'smartpay'),
        description: __('Radio fields', 'smartpay'),
        icon: page,
        keywords: ['input', 'radio'],
        attributes: {
            attributes: {
                type: Object,
                default: {
                    name: '',
                    class: '',
                    defaultValue: '',
                    options: [{ value: '', label: 'Option 1' }],
                },
            },
            settings: {
                type: Object,
                default: {
                    visible: true,
                    label: 'Radio Input',
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
