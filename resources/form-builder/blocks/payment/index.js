import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const payment = {
    namespace: 'smartpay-form/payment',
    settings: {
        title: __('Payment Fields', 'smartpay'),
        description: __('Payment fields', 'smartpay'),
        icon: page,
        keywords: ['payment'],
        attributes: {
            amounts: {
                type: Array,
                default: [{ label: '', value: 0 }],
            },
            defaultAmount: {
                type: Number,
                default: 0,
            },
            options: {
                type: Array,
                default: [{ label: '', value: 0 }],
            },
            defaultOption: {
                type: Number,
                default: 0,
            },
            allowCustomAmount: {
                type: Boolean,
                default: false,
            },
            showOptions: {
                type: Boolean,
                default: false,
            },
        },
        edit,
        save,
    },
}
