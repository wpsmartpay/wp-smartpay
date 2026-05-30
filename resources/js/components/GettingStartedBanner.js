import { useState, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { X } from 'lucide-react'

const { adminUrl } = window.smartpay

const STORAGE_KEY = 'sp_onboarding_v1'
const DISMISS_KEY = 'sp_onboarding_dismissed_v1'

const ITEMS = [
    {
        id: 1,
        label: __( 'Configure currency & settings', 'smartpay' ),
        desc:  __( 'Set your currency, business name, and payment preferences.', 'smartpay' ),
        links: [
            { label: __( 'Open Settings', 'smartpay' ), href: `${adminUrl}?page=smartpay-setting` },
        ],
    },
    {
        id: 2,
        label: __( 'Connect a payment gateway', 'smartpay' ),
        desc:  __( 'Link Stripe, PayPal, Paddle, or another provider to process payments.', 'smartpay' ),
        links: [
            { label: __( 'Open Integrations', 'smartpay' ), href: `${adminUrl}?page=smartpay-integrations` },
        ],
    },
    {
        id: 3,
        label: __( 'Create a product or form', 'smartpay' ),
        desc:  __( 'Add a product to sell or build a payment form for your site.', 'smartpay' ),
        links: [
            { label: __( 'Add Product', 'smartpay' ), href: `${adminUrl}?page=smartpay#/products/create` },
            { label: __( 'Create Form', 'smartpay' ),  href: `${adminUrl}?page=smartpay#/native-forms` },
        ],
    },
    {
        id: 4,
        label: __( 'Receive your first payment', 'smartpay' ),
        desc:  __( 'Share your form or product link — this checks automatically.', 'smartpay' ),
        links: [],
        auto: true,
    },
]

const loadChecked = () => {
    try {
        return JSON.parse( localStorage.getItem( STORAGE_KEY ) || '{}' )
    } catch {
        return {}
    }
}

const CheckIcon = ( { checked } ) => checked ? (
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style={{ flexShrink: 0 }}>
        <circle cx="10" cy="10" r="10" fill="var(--sp-brand)" />
        <path d="M6 10.5l2.8 2.8 5.2-5.8" stroke="#fff" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
) : (
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style={{ flexShrink: 0 }}>
        <circle cx="10" cy="10" r="9" stroke="#c3c4c7" strokeWidth="1.5" strokeDasharray="3 2" />
    </svg>
)

export const OnboardingChecklist = ( { dismissible = true, hasPayments = false, onLaunchWizard } ) => {
    const [checked,   setChecked]   = useState( () => loadChecked() )
    const [dismissed, setDismissed] = useState(
        () => dismissible && localStorage.getItem( DISMISS_KEY ) === '1'
    )
    const [allDone, setAllDone] = useState( false )

    const isChecked = ( id ) => id === 4 ? hasPayments : !! checked[ id ]

    const completedCount = ITEMS.filter( ( item ) => isChecked( item.id ) ).length

    const dismiss = () => {
        localStorage.setItem( DISMISS_KEY, '1' )
        setDismissed( true )
    }

    const toggle = ( id ) => {
        if ( id === 4 ) return
        const next = { ...checked, [ id ]: ! checked[ id ] }
        setChecked( next )
        localStorage.setItem( STORAGE_KEY, JSON.stringify( next ) )
    }

    useEffect( () => {
        if ( completedCount === 4 ) {
            setAllDone( true )
            if ( dismissible ) {
                const t = setTimeout( dismiss, 3000 )
                return () => clearTimeout( t )
            }
        }
    }, [completedCount] )

    if ( dismissed ) return null

    return (
        <div style={{
            background:   'var(--sp-surface)',
            border:       '1px solid var(--sp-border)',
            borderRadius: 'var(--sp-radius)',
            boxShadow:    'var(--sp-shadow)',
            marginBottom: 20,
            overflow:     'hidden',
        }}>

            {/* Header */}
            <div style={{ padding: '16px 20px 0', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--sp-text)', letterSpacing: '-.1px' }}>
                    {__( 'Getting Started', 'smartpay' )}
                </span>
                {dismissible && (
                    <button
                        type="button"
                        onClick={dismiss}
                        aria-label={__( 'Dismiss', 'smartpay' )}
                        style={{
                            background: 'none', border: 'none', cursor: 'pointer',
                            color: 'var(--sp-text-muted)', padding: 4, display: 'flex',
                            alignItems: 'center', borderRadius: 4,
                        }}
                        onMouseOver={( e ) => e.currentTarget.style.color = 'var(--sp-text)'}
                        onMouseOut={( e )  => e.currentTarget.style.color = 'var(--sp-text-muted)'}
                    >
                        <X style={{ width: 15, height: 15 }} />
                    </button>
                )}
            </div>

            {/* Progress bar */}
            <div style={{ padding: '8px 20px 0' }}>
                <div style={{ height: 4, background: 'var(--sp-border)', borderRadius: 99, overflow: 'hidden' }}>
                    <div style={{
                        height: '100%',
                        width:  `${( completedCount / 4 ) * 100}%`,
                        background: 'var(--sp-brand)',
                        borderRadius: 99,
                        transition: 'width .3s ease',
                    }} />
                </div>
                <div style={{ fontSize: 11, color: 'var(--sp-text-muted)', marginTop: 4 }}>
                    {completedCount} / 4 {__( 'complete', 'smartpay' )}
                </div>
            </div>

            {/* All done state */}
            {allDone ? (
                <div style={{ padding: '20px 20px 22px', textAlign: 'center' }}>
                    <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--sp-text)', marginBottom: 4 }}>
                        {__( "You're all set! Payments are ready to go.", 'smartpay' )}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--sp-text-muted)' }}>
                        {dismissible ? __( 'This panel will close shortly.', 'smartpay' ) : __( 'All setup steps are complete.', 'smartpay' )}
                    </div>
                </div>
            ) : (
                <>
                    {/* Checklist items */}
                    <ul style={{ listStyle: 'none', margin: '12px 0 0', padding: 0 }}>
                        {ITEMS.map( ( item, i ) => {
                            const done = isChecked( item.id )
                            return (
                                <li
                                    key={item.id}
                                    onClick={() => toggle( item.id )}
                                    style={{
                                        display:    'flex',
                                        alignItems: 'flex-start',
                                        gap:        12,
                                        padding:    '10px 20px',
                                        borderTop:  i > 0 ? '1px solid var(--sp-border)' : 'none',
                                        cursor:     item.auto ? 'default' : 'pointer',
                                    }}
                                    onMouseOver={( e ) => { if ( ! item.auto ) e.currentTarget.style.background = 'var(--sp-surface-muted, #f6f7f7)' }}
                                    onMouseOut={( e )  => { e.currentTarget.style.background = 'transparent' }}
                                >
                                    <div style={{ marginTop: 1 }}>
                                        <CheckIcon checked={done} />
                                    </div>
                                    <div style={{ flex: 1, minWidth: 0 }}>
                                        <div style={{ fontSize: 13, fontWeight: 600, color: done ? 'var(--sp-text-muted)' : 'var(--sp-text)', textDecoration: done ? 'line-through' : 'none', lineHeight: 1.35 }}>
                                            {item.label}
                                        </div>
                                        {! done && (
                                            <div style={{ fontSize: 11.5, color: 'var(--sp-text-muted)', marginTop: 2, lineHeight: 1.5 }}>
                                                {item.desc}
                                            </div>
                                        )}
                                    </div>
                                    {! done && item.links.length > 0 && (
                                        <div style={{ display: 'flex', gap: 10, alignItems: 'center', flexShrink: 0 }} onClick={( e ) => e.stopPropagation()}>
                                            {item.links.map( ( link ) => (
                                                <a
                                                    key={link.href}
                                                    href={link.href}
                                                    style={{ fontSize: 12, color: 'var(--sp-brand)', fontWeight: 600, textDecoration: 'none', whiteSpace: 'nowrap' }}
                                                    onMouseOver={( e ) => e.currentTarget.style.textDecoration = 'underline'}
                                                    onMouseOut={( e )  => e.currentTarget.style.textDecoration = 'none'}
                                                >
                                                    {link.label} →
                                                </a>
                                            ) )}
                                        </div>
                                    )}
                                </li>
                            )
                        } )}
                    </ul>

                    {/* Footer */}
                    {onLaunchWizard && (
                        <div style={{ padding: '10px 20px 14px', borderTop: '1px solid var(--sp-border)' }}>
                            <button
                                type="button"
                                onClick={onLaunchWizard}
                                style={{ background: 'none', border: 'none', padding: 0, cursor: 'pointer', fontSize: 12, color: 'var(--sp-brand)', fontWeight: 600 }}
                                onMouseOver={( e ) => e.currentTarget.style.textDecoration = 'underline'}
                                onMouseOut={( e )  => e.currentTarget.style.textDecoration = 'none'}
                            >
                                {__( 'Run Setup Wizard →', 'smartpay' )}
                            </button>
                        </div>
                    )}
                </>
            )}
        </div>
    )
}

// Backwards-compatible default export keeps existing import sites working.
export const GettingStartedBanner = OnboardingChecklist
export default OnboardingChecklist
