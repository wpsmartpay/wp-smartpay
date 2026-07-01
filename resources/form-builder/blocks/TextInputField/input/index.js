import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Text Input — child of smartpay-form/text-input. Renders the real input and
 * keeps a unique `smartpay_form[<fieldName>]` submission name.
 */
export const TextInputInput = {
    namespace: 'smartpay-form/text-input-input',
    settings: {
        title: __('Text Input', 'smartpay'),
        description: __('The text input field.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/text-input'],
        keywords: ['input', 'text', 'number'],
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
            inputType: { type: 'string', default: 'text' }, // text | number | email
            placeholder: { type: 'string', default: '' },
            isRequired: { type: 'boolean', default: false },
        },
        edit,
        save,
    },
}
