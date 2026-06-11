import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Select Input — child of smartpay-form/select-input. Renders the <select> and
 * owns the options + a unique submission name.
 */
export const SelectInput = {
    namespace: 'smartpay-form/select-input-input',
    settings: {
        title: __('Select Input', 'smartpay'),
        description: __('The select dropdown.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/select-input'],
        keywords: ['input', 'select', 'dropdown'],
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
            defaultValue: { type: 'string', default: '' },
            options: {
                type: 'array',
                default: [{ value: '', label: 'Option 1' }],
            },
        },
        edit,
        save,
    },
}
