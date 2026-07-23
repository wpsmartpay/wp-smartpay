import { __ } from '@wordpress/i18n'
import { typography } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const AddressLabel = {
    namespace: 'smartpay-form/address-label',
    settings: {
        title: __('Address Label', 'smartpay'),
        description: __('Label for an address line.', 'smartpay'),
        icon: typography,
        parent: ['smartpay-form/address-field'],
        keywords: ['label', 'address'],
        supports: { html: false, reusable: false, customClassName: false },
        attributes: {
            text: { type: 'string', default: 'Address' },
            htmlFor: { type: 'string', default: '' },
        },
        edit,
        save,
    },
}
