import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const CheckboxLabel = {
    namespace: 'smartpay-form/checkbox-input-label',
    settings: {
        title: __('Checkbox Label', 'smartpay'),
        description: __('Label for the checkbox field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/checkbox-input'],
        keywords: ['label', 'checkbox'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Checkbox Input' },
        },
        edit,
        save,
    },
}
