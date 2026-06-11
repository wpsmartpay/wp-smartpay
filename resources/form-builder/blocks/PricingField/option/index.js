import { __ } from '@wordpress/i18n'
import { currencyDollar } from '@wordpress/icons'
import { edit } from './edit'
import { save } from './save'

/**
 * Pricing Option — child of smartpay-form/pricing.
 *
 * One selectable price card. Carries native color / border / typography
 * supports so each option is styled independently with WordPress's standard
 * block panels (no custom controls). Subscription billing is Pro-gated.
 */
export const PricingOption = {
    namespace: 'smartpay-form/pricing-option',
    settings: {
        title: __('Pricing Option', 'smartpay'),
        description: __('A single selectable price option.', 'smartpay'),
        icon: currencyDollar,
        parent: ['smartpay-form/pricing'],
        supports: {
            html: false,
            reusable: false,
            color: {
                text: true,
                background: true,
                __experimentalDefaultControls: { background: true, text: true },
            },
            __experimentalBorder: {
                color: true,
                radius: true,
                style: true,
                width: true,
                __experimentalDefaultControls: {
                    color: true,
                    radius: true,
                    width: true,
                },
            },
            typography: {
                fontSize: true,
                __experimentalDefaultControls: { fontSize: true },
            },
            spacing: {
                padding: true,
                __experimentalDefaultControls: { padding: true },
            },
        },
        attributes: {
            key: { type: 'string' },
            label: { type: 'string', default: 'Plan' },
            description: { type: 'string', default: '' },
            amount: { type: 'string', default: '0' },
            billing_type: { type: 'string', default: 'One Time' },
            billing_period: { type: 'string', default: 'month' },
            setup_fee: { type: 'string', default: '0' },
            billing_cycle: { type: 'string', default: '' },
        },
        __experimentalLabel: (attributes) => {
            return attributes.label || __('Pricing Option', 'smartpay')
        },
        edit,
        save,
    },
}
