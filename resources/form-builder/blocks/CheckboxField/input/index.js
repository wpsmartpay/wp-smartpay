import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Checkbox Input — child of smartpay-form/checkbox-input. Renders the checkbox
 * options group and owns a unique submission name.
 */
export const CheckboxInput = {
    namespace: 'smartpay-form/checkbox-input-input',
    settings: {
        title: __('Checkbox Options', 'smartpay'),
        description: __('The checkbox options.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/checkbox-input'],
        keywords: ['input', 'checkbox', 'options'],
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
