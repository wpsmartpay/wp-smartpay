import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const CheckboxField = {
    namespace: 'smartpay-form/checkbox-input',
    settings: {
        title: __('Checkbox Fields', 'smartpay'),
        description: __('Checkbox fields', 'smartpay'),
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
                    label: 'Checkbox Input',
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
