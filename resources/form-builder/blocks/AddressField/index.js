import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Address field (parent container). One per form.
 *
 * Holds an Address Field child per line (Address 1/2, City, State, Zip,
 * Country); each holds its own Label + Input. Inputs submit as
 * smartpay_form[address][<line>].
 */
export const AddressField = {
    namespace: 'smartpay-form/address-input',
    settings: {
        title: __('Address Fields', 'smartpay'),
        description: __('Address field — each line a label + input.', 'smartpay'),
        icon: page,
        keywords: ['input', 'address'],
        supports: {
            html: false,
            multiple: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            showLine1:   { type: 'boolean', default: true },
            showLine2:   { type: 'boolean', default: true },
            showCity:    { type: 'boolean', default: true },
            showState:   { type: 'boolean', default: true },
            showZip:     { type: 'boolean', default: true },
            showCountry: { type: 'boolean', default: true },
        },
        edit,
        save,
    },
}
