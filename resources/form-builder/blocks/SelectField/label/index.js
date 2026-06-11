import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const SelectLabel = {
    namespace: 'smartpay-form/select-input-label',
    settings: {
        title: __('Select Label', 'smartpay'),
        description: __('Label for the select field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/select-input'],
        keywords: ['label', 'select'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Select Field' },
        },
        edit,
        save,
    },
}
