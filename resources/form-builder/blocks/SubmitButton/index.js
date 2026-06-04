import { __ } from '@wordpress/i18n'
import { button } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'
import './editor.scss'

/**
 * Submit Button — the form's submit / pay action. One per form.
 *
 * This block is an editor-side CONFIGURATOR: its save() renders nothing on the
 * frontend. The form embed template (native-form-embed.php) reads this block's
 * attributes and renders the real `<button class="smartpay-form-pay-now">`
 * AFTER the payment-gateway selector, so the action always sits last — the
 * gateways are template-rendered after do_blocks(), so a body-level button
 * would otherwise appear above them. Styling is stored as explicit attributes
 * and applied as inline styles server-side (native block supports cannot
 * serialize through a null save).
 */
export const SubmitButton = {
    namespace: 'smartpay-form/submit-button',
    settings: {
        title: __('Submit Button', 'smartpay'),
        description: __(
            "The form's submit / pay button. Only one per form.",
            'smartpay'
        ),
        icon: button,
        keywords: ['pay', 'submit', 'button', 'checkout', 'buy', 'order'],
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
            customIconUrl: { type: 'string', default: '' }, // media library / uploaded SVG|image
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
