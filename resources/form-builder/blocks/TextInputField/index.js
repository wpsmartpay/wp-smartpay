import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Text Input field (parent container) — holds a Label + Input child.
 */
export const TextInputField = {
    namespace: 'smartpay-form/text-input',
    settings: {
        title: __('Text Input Fields', 'smartpay'),
        description: __('Text input field — label + input.', 'smartpay'),
        icon: page,
        keywords: ['input', 'text', 'number', 'email'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
