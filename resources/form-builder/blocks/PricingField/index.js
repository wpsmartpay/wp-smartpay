import { __ } from '@wordpress/i18n'
import { currencyDollar } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'
import './editor.scss'

/**
 * Pricing block (parent / container).
 *
 * Holds N `smartpay-form/pricing-option` child blocks. Layout presets are
 * native block styles (Grid / List / Compact); per-option + container styling
 * use native block supports, which layer on top of the chosen preset. Pricing
 * data flows block → `_smartpay_amounts` meta on save (see NativeForm sync).
 */
export const PricingField = {
    namespace: 'smartpay-form/pricing',
    settings: {
        title: __('Pricing', 'smartpay'),
        description: __(
            'Selectable price options as cards. Choose a layout, style each option.',
            'smartpay'
        ),
        icon: currencyDollar,
        keywords: ['price', 'pricing', 'amount', 'plan', 'subscription'],
        // Mirrors core/buttons: native Layout (justification/orientation/wrap),
        // Color (background), Typography (font size), Dimensions (padding/margin)
        // and Block Spacing (blockGap → the gap between options).
        supports: {
            anchor: true,
            html: false,
            multiple: false,
            reusable: false,
            color: {
                background: true,
                gradients: true,
                text: false,
                __experimentalDefaultControls: { background: true },
            },
            typography: {
                fontSize: true,
                lineHeight: true,
                __experimentalDefaultControls: { fontSize: true },
            },
            spacing: {
                blockGap: ['horizontal', 'vertical'],
                padding: true,
                margin: ['top', 'bottom'],
                __experimentalDefaultControls: { blockGap: true },
            },
            __experimentalBorder: {
                color: true,
                radius: true,
                style: true,
                width: true,
            },
            layout: {
                allowSwitching: false,
                allowInheriting: false,
                default: { type: 'flex', flexWrap: 'wrap' },
            },
        },
        // Block styles registered explicitly in blocks/index.js (Grid default).
        attributes: {
            preset: { type: 'string', default: 'grid' },
            allowCustomAmount: { type: 'boolean', default: false },
            customAmountLabel: { type: 'string', default: 'Enter custom amount' },
            currencySymbol: { type: 'string', default: '$' },
            customInputBackground: { type: 'string', default: '' },
            customInputBorder: { type: 'string', default: '' },
        },
        edit,
        save,
    },
}
