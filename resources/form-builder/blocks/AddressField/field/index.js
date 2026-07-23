import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Address Field line — child of smartpay-form/address-input. A single address
 * line holding its own Label + Input pair.
 */
export const AddressFieldLine = {
    namespace: 'smartpay-form/address-field',
    settings: {
        title: __('Address Field', 'smartpay'),
        description: __('A single address line (label + input).', 'smartpay'),
        icon: page,
        parent: ['smartpay-form/address-input'],
        keywords: ['address', 'field'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            label: { type: 'string', default: '' },
            fieldType: { type: 'string', default: '' },
        },
        __experimentalLabel: (attributes) => {
            return attributes.label || __('Address Field', 'smartpay')
        },
        edit,
        save,
    },
}
