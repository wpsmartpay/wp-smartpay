import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Radio field (parent container) — holds a Label + Input (options) child.
 */
export const RadioField = {
    namespace: 'smartpay-form/radio-input',
    settings: {
        title: __('Radio Fields', 'smartpay'),
        description: __('Radio field — label + options.', 'smartpay'),
        icon: page,
        keywords: ['input', 'radio'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
