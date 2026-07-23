import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Text Area field (parent container) — holds a Label + Input child.
 */
export const TextAreaField = {
    namespace: 'smartpay-form/textarea-input',
    settings: {
        title: __('Text Area Fields', 'smartpay'),
        description: __('Text area field — label + input.', 'smartpay'),
        icon: page,
        keywords: ['input', 'text', 'textarea'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
