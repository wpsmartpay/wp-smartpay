import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Address Input — child of smartpay-form/address-field. Submits as
 * smartpay_form[address][<fieldName>].
 */
export const AddressInput = {
    namespace: 'smartpay-form/address-input-field',
    settings: {
        title: __('Address Input', 'smartpay'),
        description: __('An address line input.', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/address-field'],
        keywords: ['input', 'address'],
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
            fieldName: { type: 'string', default: 'line_1' },
            fieldType: { type: 'string', default: 'text' },
            placeholder: { type: 'string', default: '' },
            isRequired: { type: 'boolean', default: false },
        },
        edit,
        save,
    },
}
