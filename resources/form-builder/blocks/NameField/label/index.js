import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const NameLabel = {
    namespace: 'smartpay-form/name-label',
    settings: {
        title: __('Name Label', 'smartpay'),
        description: __('Label for a name sub-field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/name-field'],
        keywords: ['label', 'name'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Name' },
            htmlFor: { type: 'string', default: '' },
        },
        edit,
        save,
    },
}
