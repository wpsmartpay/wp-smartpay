import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Select field (parent container) — holds a Label + Input child.
 */
export const SelectField = {
    namespace: 'smartpay-form/select-input',
    settings: {
        title: __('Select Fields', 'smartpay'),
        description: __('Select field — label + input.', 'smartpay'),
        icon: page,
        keywords: ['input', 'select'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
