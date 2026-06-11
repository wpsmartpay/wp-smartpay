import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const TextInputLabel = {
    namespace: 'smartpay-form/text-input-label',
    settings: {
        title: __('Text Input Label', 'smartpay'),
        description: __('Label for the text input field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/text-input'],
        keywords: ['label', 'text'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Text Input' },
        },
        edit,
        save,
    },
}
