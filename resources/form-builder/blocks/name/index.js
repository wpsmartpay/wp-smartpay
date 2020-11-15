import { __ } from '@wordpress/i18n'
import { page } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

export const name = {
	namespace: 'smartpay-form-element/name',
	settings: {
		title: __('Name Fields'),
		description: __('Name fields'),
		icon: page,
		keywords: [__('name', 'first name')],
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
