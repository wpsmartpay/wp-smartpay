import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Name Input — child of smartpay-form/name-field. Submits as
 * smartpay_form[name][<fieldName>] (first_name / middle_name / last_name).
 */
export const NameInput = {
    namespace: 'smartpay-form/name-input',
    settings: {
        title: __('Name Input', 'smartpay'),
        description: __('A name sub-field input.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/name-field'],
        keywords: ['input', 'name'],
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
            fieldName: { type: 'string', default: 'first_name' },
            placeholder: { type: 'string', default: '' },
            isRequired: { type: 'boolean', default: false },
        },
        edit,
        save,
    },
}
