import { __ } from '@wordpress/i18n'
import { tag } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Coupon — child of smartpay-form/submit-button.
 *
 * Editor-side configurator: save() renders nothing. The Coupon module reads
 * these attributes to render the real coupon section (with its AJAX apply
 * wiring) just before the pay button. Remove this child to hide the coupon
 * section for the form. Requires "Enable coupons at form" in SmartPay settings.
 */
export const SubmitCoupon = {
    namespace: 'smartpay-form/submit-coupon',
    settings: {
        title: __('Coupon', 'smartpay'),
        description: __('A collapsible coupon-code field shown above the pay button.', 'smartpay'),
        icon: tag,
        parent: ['smartpay-form/submit-button'],
        keywords: ['coupon', 'discount', 'promo', 'code'],
        supports: {
            html: false,
            multiple: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            toggleLabel: { type: 'string', default: 'Have a coupon?' },
            placeholder: { type: 'string', default: 'Coupon code' },
            applyLabel: { type: 'string', default: 'Apply' },
            accentColor: { type: 'string', default: '#28a745' },
        },
        edit,
        save,
    },
}
