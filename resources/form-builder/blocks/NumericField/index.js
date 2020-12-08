import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const NumericField = {
    namespace: 'smartpay-form/numeric-input',
    settings: {
        title: __('Numeric Field', 'smartpay'),
        description: __('Numeric Field', 'smartpay'),
        icon: page,
        keywords: ['input', 'number'],
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
                    label: 'Number Input',
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
