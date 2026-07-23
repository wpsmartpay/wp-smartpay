import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const RadioLabel = {
    namespace: 'smartpay-form/radio-input-label',
    settings: {
        title: __('Radio Label', 'smartpay'),
        description: __('Label for the radio field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/radio-input'],
        keywords: ['label', 'radio'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Radio Input' },
        },
        edit,
        save,
    },
}
