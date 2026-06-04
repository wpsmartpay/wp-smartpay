import { __ } from '@wordpress/i18n'
import { button } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'
import './editor.scss'

/**
 * Submit Button — the form's submit area (container). One per form.
 *
 * Holds two child blocks: an optional Coupon block and the Pay Button block.
 * Like the rest of the submit area it is an editor-side configurator — the
 * children's save() render nothing; the form embed template + Coupon module
 * read the children's attributes and render the real markup after the payment
 * gateway selector, so the submit area always sits last. Remove the Coupon
 * child to hide the coupon section for that form.
 */
export const SubmitButton = {
    namespace: 'smartpay-form/submit-button',
    settings: {
        title: __('Submit Button', 'smartpay'),
        description: __(
            "The form's submit area — pay button and optional coupon. One per form.",
            'smartpay'
        ),
        icon: button,
        keywords: ['pay', 'submit', 'button', 'checkout', 'buy', 'order', 'coupon'],
        supports: {
            html: false,
            multiple: false,
            reusable: false,
            customClassName: false,
        },
        edit,
        save,
    },
}
