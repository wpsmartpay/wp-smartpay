import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Name field (parent container). One per form.
 *
 * Holds a Name Field (column) child per sub-field (First / Middle / Last); each
 * column holds its own Label + Input pair. Inputs submit as
 * smartpay_form[name][<sub>] so the payment flow is unchanged.
 */
export const NameField = {
    namespace: 'smartpay-form/name',
    settings: {
        title: __('Name Fields', 'smartpay'),
        description: __('Name field — first / middle / last, each a label + input.', 'smartpay'),
        icon: page,
        keywords: ['name', 'first name', 'last name'],
        supports: {
            html: false,
            multiple: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            showFirstName: { type: 'boolean', default: true },
            showMiddleName: { type: 'boolean', default: true },
            showLastName: { type: 'boolean', default: true },
        },
        edit,
        save,
    },
}
