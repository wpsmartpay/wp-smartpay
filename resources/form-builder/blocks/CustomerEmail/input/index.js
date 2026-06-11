import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Email Input — child of smartpay-form/email.
 *
 * Renders the actual email `<input>` and keeps the `smartpay_form[email]`
 * submission name so the payment flow is unchanged. Color / typography /
 * spacing come from native block panels (granted by the category script);
 * border is declared here so the Border panel is available too.
 */
export const EmailInput = {
    namespace: 'smartpay-form/email-input',
    settings: {
        title: __('Email Input', 'smartpay'),
        description: __('The email input field.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/email'],
        keywords: ['input', 'email'],
        supports: {
            html: false,
            reusable: false,
            customClassName: false,
            __experimentalBorder: {
                color: true,
                radius: true,
                style: true,
                width: true,
                __experimentalDefaultControls: { radius: true, width: true },
            },
        },
        attributes: {
            fieldName: { type: 'string', default: 'email' },
            placeholder: { type: 'string', default: 'Email' },
            isRequired: { type: 'boolean', default: true },
        },
        edit,
        save,
    },
}
