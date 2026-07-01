import { __ } from '@wordpress/i18n'
import { button } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Pay Button — child of smartpay-form/submit-button.
 *
 * Editor-side configurator: save() renders nothing. The form embed template
 * reads these attributes and renders the real button.smartpay-form-pay-now
 * after the gateway selector, preserving the frontend payment contract.
 */
export const SubmitPay = {
    namespace: 'smartpay-form/submit-pay',
    settings: {
        title: __('Pay Button', 'smartpay'),
        description: __('The pay / submit button.', 'smartpay'),
        icon: button,
        parent: ['smartpay-form/submit-button'],
        keywords: ['pay', 'submit', 'button', 'checkout'],
        supports: {
            html: false,
            multiple: false,
            reusable: false,
            customClassName: false,
        },
        attributes: {
            label: { type: 'string', default: 'Pay Now' },
            iconType: { type: 'string', default: 'preset' }, // preset | custom
            icon: { type: 'string', default: '' }, // preset slug; '' = none
            customIconUrl: { type: 'string', default: '' },
            customIconId: { type: 'number', default: 0 },
            iconPosition: { type: 'string', default: 'left' }, // left | right
            align: { type: 'string', default: 'left' }, // left | center | right
            width: { type: 'number', default: 0 }, // 0 = auto, else 25/50/75/100
            fullWidth: { type: 'boolean', default: false },
            bgColor: { type: 'string', default: '#28a745' },
            textColor: { type: 'string', default: '#ffffff' },
            borderColor: { type: 'string', default: '' },
            borderWidth: { type: 'number', default: 0 },
            borderRadius: { type: 'number', default: 6 },
            fontSize: { type: 'number', default: 16 },
            fontWeight: { type: 'string', default: '600' },
            paddingY: { type: 'number', default: 14 },
            paddingX: { type: 'number', default: 24 },
        },
        edit,
        save,
    },
}
