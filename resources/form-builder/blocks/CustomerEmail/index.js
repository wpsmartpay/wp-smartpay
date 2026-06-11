import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Email field (parent container). One per form.
 *
 * Holds two child blocks: a Label (smartpay-form/email-label) and an Input
 * (smartpay-form/email-input), each independently selectable + stylable in
 * List View. The parent only renders the `.form-element` wrapper; the children
 * render the real label + input. The input keeps the `smartpay_form[email]`
 * submission name so the payment flow is unchanged.
 */
export const CustomerEmail = {
    namespace: 'smartpay-form/email',
    settings: {
        title: __('Email Fields', 'smartpay'),
        description: __('Email field — label + input.', 'smartpay'),
        icon: page,
        keywords: ['email'],
        supports: {
            html: false,
            multiple: false, // one Email field per form
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
