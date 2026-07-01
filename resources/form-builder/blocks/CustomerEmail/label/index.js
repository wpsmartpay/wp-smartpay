import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Email Label — child of smartpay-form/email.
 *
 * Just the field's <label>. Styling (text color, font size, spacing) comes from
 * WordPress's native block panels — the form-builder category script grants
 * color/typography/spacing supports to every smartpay-form block, so they apply
 * here automatically.
 */
export const EmailLabel = {
    namespace: 'smartpay-form/email-label',
    settings: {
        title: __('Email Label', 'smartpay'),
        description: __('Label for the email field.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/email'],
        keywords: ['label', 'email'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            text: { type: 'string', default: 'Email' },
        },
        edit,
        save,
    },
}
