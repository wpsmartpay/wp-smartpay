import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const email = {
    namespace: 'smartpay-form/email',
    settings: {
        title: __('Email Fields', 'smartpay'),
        description: __('Email fields', 'smartpay'),
        icon: page,
        keywords: ['email'],
        attributes: {},
        edit,
        save,
    },
}
