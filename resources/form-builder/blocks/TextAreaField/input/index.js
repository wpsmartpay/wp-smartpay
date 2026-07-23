import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Text Area Input — child of smartpay-form/textarea-input.
 */
export const TextAreaInput = {
    namespace: 'smartpay-form/textarea-input-input',
    settings: {
        title: __('Text Area Input', 'smartpay'),
        description: __('The text area input field.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/textarea-input'],
        keywords: ['input', 'textarea'],
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
            fieldName: { type: 'string', default: '' },
            placeholder: { type: 'string', default: '' },
            isRequired: { type: 'boolean', default: false },
            rows: { type: 'number', default: 3 },
        },
        edit,
        save,
    },
}
