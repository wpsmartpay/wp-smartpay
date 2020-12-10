import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const SelectField = {
    namespace: 'smartpay-form/select-input',
    settings: {
        title: __('Select Fields', 'smartpay'),
        description: __('Select fields', 'smartpay'),
        icon: page,
        keywords: ['input', 'select'],
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
                    label: 'Select Field',
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
