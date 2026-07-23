import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Radio Input — child of smartpay-form/radio-input. Renders the radio options
 * group and owns a unique submission name.
 */
export const RadioInput = {
    namespace: 'smartpay-form/radio-input-input',
    settings: {
        title: __('Radio Options', 'smartpay'),
        description: __('The radio button options.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/radio-input'],
        keywords: ['input', 'radio', 'options'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            fieldName: { type: 'string', default: '' },
            defaultValue: { type: 'string', default: '' },
            options: {
                type: 'array',
                default: [{ value: '', label: 'Option 1' }],
            },
        },
        edit,
        save,
    },
}
