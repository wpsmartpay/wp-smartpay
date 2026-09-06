import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

// ── Data ──────────────────────────────────────────────────────

const SHORTCODES = [
    {
        tag:     'sp_form',
        desc:    __('Renders a native SmartPay form (CPT) inline on any page. Use this for new builds.', 'smartpay'),
        source:  'Free · Pro overrides with template styling',
    },
    {
        tag:     'smartpay_dashboard',
        desc:    __('WooCommerce-style customer account dashboard (orders, subscriptions, downloads). Requires the visitor to be logged in.', 'smartpay'),
        source:  'Free',
    },
    {
        tag:     'smartpay_user_profile',
        desc:    __('Edit-profile page (personal info, address, change password). Requires the visitor to be logged in.', 'smartpay'),
        source:  'Free',
    },
    {
        tag:     'smartpay_user_registration',
        desc:    __('Frontend user registration form. Creates a SmartPay customer account on submit.', 'smartpay'),
        source:  'Free',
    },
    {
        tag:     'smartpay_user_login',
        desc:    __('Frontend customer login form. Redirects to the dashboard on success.', 'smartpay'),
        source:  'Free',
    },
    {
        tag:     'smartpay_payment_receipt',
        desc:    __('Shows the payment receipt for the current ?smartpay-payment=<uuid> URL. Typically used as the "return URL" on gateway settings.', 'smartpay'),
        source:  'Free',
    },
    // Legacy shortcodes — kept for backwards compatibility on existing installs.
    {
        tag:     'smartpay_form',
        desc:    __('Legacy tag. Renders an older-generation payment form by ID. Auto-delegates to [sp_form] when the form has been migrated to the native CPT. Prefer [sp_form] for new builds.', 'smartpay'),
        source:  'Free (legacy)',
    },
    {
        tag:     'smartpay_product',
        desc:    __('Legacy tag. Renders a Buy button for a product by ID. Supports popup and inline behavior. Still used by Pro product templates.', 'smartpay'),
        source:  'Free (legacy) · Pro overrides with template styling',
    },
]

// ── Icons ─────────────────────────────────────────────────────

const CopyIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
    </svg>
)

const CheckIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
        <path strokeLinecap="round" strokeLinejoin="round" d="m4.5 12.75 6 6 9-13.5" />
    </svg>
)

// ── Components ────────────────────────────────────────────────

function CopyButton({ text }) {
    const [copied, setCopied] = useState(false)

    const copy = (e) => {
        e.preventDefault()
        e.stopPropagation()
        navigator.clipboard.writeText(text).then(() => {
            setCopied(true)
            setTimeout(() => setCopied(false), 1500)
        })
    }

    return (
        <button
            type="button"
            onClick={copy}
            aria-label={__('Copy shortcode', 'smartpay')}
            title={copied ? __('Copied!', 'smartpay') : __('Copy', 'smartpay')}
            style={{
                alignItems: 'center',
                background: 'transparent',
                border: '1px solid var(--sp-border)',
                borderRadius: 6,
                color: copied ? 'var(--sp-success, #10b981)' : 'var(--sp-text-muted)',
                cursor: 'pointer',
                display: 'inline-flex',
                flexShrink: 0,
                height: 26,
                justifyContent: 'center',
                padding: 0,
                transition: 'color .15s, border-color .15s',
                width: 28,
            }}
        >
            {copied ? <CheckIcon /> : <CopyIcon />}
        </button>
    )
}

function ShortcodeRow({ item }) {
    return (
        <tr style={{ borderBottom: '1px solid var(--sp-border)' }}>
            <td style={{ padding: '14px 20px', verticalAlign: 'top', width: 1, whiteSpace: 'nowrap' }}>
                <div style={{ alignItems: 'center', display: 'flex', gap: 8 }}>
                    <code style={{
                        background: 'var(--sp-surface-muted, #f3f4f6)',
                        border: '1px solid var(--sp-border)',
                        borderRadius: 6,
                        color: 'var(--sp-text)',
                        fontFamily: 'ui-monospace, SFMono-Regular, Menlo, monospace',
                        fontSize: 12.5,
                        fontWeight: 500,
                        padding: '4px 9px',
                    }}>[{item.tag}]</code>
                    <CopyButton text={`[${item.tag}]`} />
                </div>
            </td>
            <td style={{ padding: '14px 20px', color: 'var(--sp-text)', fontSize: 13, lineHeight: 1.55, verticalAlign: 'top' }}>
                {item.desc}
            </td>
            <td style={{ padding: '14px 20px', color: 'var(--sp-text-muted)', fontSize: 12, verticalAlign: 'top', whiteSpace: 'nowrap' }}>
                {item.source}
            </td>
        </tr>
    )
}

export function Shortcodes() {
    return (
        <div className="sp-detail-card">
            <div className="sp-detail-card__header">
                <div>
                    <span className="sp-detail-card__title">{__('Plugin Shortcodes', 'smartpay')}</span>
                    <p style={{ color: 'var(--sp-text-muted)', fontSize: '12.5px', margin: '4px 0 0' }}>
                        {__('Drop these into any page or post to render SmartPay components.', 'smartpay')}
                    </p>
                </div>
            </div>

            <div className="sp-detail-card__body" style={{ padding: 0 }}>
                <table style={{ borderCollapse: 'collapse', width: '100%' }}>
                    <thead>
                        <tr style={{ background: 'var(--sp-surface-muted, #f9fafb)', borderBottom: '1px solid var(--sp-border)' }}>
                            <th style={{ color: 'var(--sp-text-muted)', fontSize: 11, fontWeight: 600, padding: '10px 20px', textAlign: 'left', textTransform: 'uppercase', width: 1, whiteSpace: 'nowrap' }}>
                                {__('Shortcode', 'smartpay')}
                            </th>
                            <th style={{ color: 'var(--sp-text-muted)', fontSize: 11, fontWeight: 600, padding: '10px 20px', textAlign: 'left', textTransform: 'uppercase' }}>
                                {__('Description', 'smartpay')}
                            </th>
                            <th style={{ color: 'var(--sp-text-muted)', fontSize: 11, fontWeight: 600, padding: '10px 20px', textAlign: 'left', textTransform: 'uppercase', whiteSpace: 'nowrap' }}>
                                {__('Available in', 'smartpay')}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {SHORTCODES.map((item) => (
                            <ShortcodeRow key={item.tag} item={item} />
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    )
}