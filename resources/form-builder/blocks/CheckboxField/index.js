import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Checkbox field (parent container) — holds a Label + Input (options) child.
 */
export const CheckboxField = {
    namespace: 'smartpay-form/checkbox-input',
    settings: {
        title: __('Checkbox Fields', 'smartpay'),
        description: __('Checkbox field — label + options.', 'smartpay'),
        icon: page,
        keywords: ['input', 'checkbox'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
