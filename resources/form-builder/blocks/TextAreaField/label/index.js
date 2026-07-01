import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const TextAreaLabel = {
    namespace: 'smartpay-form/textarea-input-label',
    settings: {
        title: __('Text Area Label', 'smartpay'),
        description: __('Label for the text area field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/textarea-input'],
        keywords: ['label', 'textarea'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Text Area' },
        },
        edit,
        save,
    },
}
