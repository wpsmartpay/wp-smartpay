import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Name Field column — child of smartpay-form/name. A single sub-field column
 * (First / Middle / Last) holding its own Label + Input pair.
 */
export const NameFieldColumn = {
    namespace: 'smartpay-form/name-field',
    settings: {
        title: __('Name Field', 'smartpay'),
        description: __('A single name sub-field (label + input).', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/name'],
        keywords: ['name', 'field'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            label: { type: 'string', default: '' },
            fieldType: { type: 'string', default: '' },
        },
        __experimentalLabel: (attributes) => {
            return attributes.label || __('Name Field', 'smartpay')
        },
        edit,
        save,
    },
}
