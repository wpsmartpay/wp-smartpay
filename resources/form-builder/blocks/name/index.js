import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const name = {
    namespace: 'smartpay-form/name',
    settings: {
        title: __('Name Fields', 'smartpay'),
        description: __('Name fields', 'smartpay'),
        icon: page,
        keywords: ['name', 'first name'],
        attributes: {
            showLastName: {
                type: Boolean,
                default: true,
            },
        },
        edit,
        save,
    },
}
