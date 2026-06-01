import { useState, useEffect, useCallback } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'

const { adminUrl, apiNonce, restUrl, options = {} } = window.smartpay

const WIZARD_KEY = 'sp_wizard_v1_shown'

// restUrl = 'http://site.com/wp-json/smartpay' (no /v1) — must add /v1/ explicitly
const buildUrl = ( path ) => new URL( `${restUrl}/v1/${path}` ).toString()

// ── Step dots ─────────────────────────────────────────────────

function StepDots( { current, total } ) {
    return (
        <div style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
            {Array.from( { length: total }, ( _, i ) => i + 1 ).map( ( n ) => (
                <div
                    key={n}
                    style={{
                        width:        n === current ? 20 : 8,
                        height:       8,
                        borderRadius: 99,
                        background:   n === current ? '#293c81' : n < current ? '#293c81' : '#d1d5db',
                        opacity:      n < current ? 0.4 : 1,
                        transition:   'all .25s ease',
                    }}
                />
            ) )}
        </div>
    )
}

// ── Step 1 ────────────────────────────────────────────────────

function Step1( { onNext } ) {
    const [currency,     setCurrency]     = useState( options.currency || 'USD' )
    const [businessName, setBusinessName] = useState( options.businessName || '' )
    const [saving,       setSaving]       = useState( false )
    const [error,        setError]        = useState( '' )
    const [focusCur,     setFocusCur]     = useState( false )
    const [focusBiz,     setFocusBiz]     = useState( false )

    const currencies = options.currencies || {}

    const save = async () => {
        setSaving( true )
        setError( '' )
        try {
            await apiFetch( {
                url:     buildUrl( 'wizard/setup' ),
                method:  'POST',
                headers: { 'X-WP-Nonce': apiNonce },
                data:    { currency, business_name: businessName },
            } )
            onNext()
        } catch ( err ) {
            setError( err?.message || __( 'Something went wrong. Please try again.', 'smartpay' ) )
            setSaving( false )
        }
    }

    return (
        <div style={bodyStyle}>
            <div style={stepBadgeStyle}>01</div>
            <h2 style={headingStyle}>{__( "Let's set up your store", 'smartpay' )}</h2>
            <p style={subStyle}>{__( 'Configure your currency and business details. You can change these anytime from Settings.', 'smartpay' )}</p>

            <div style={{ display: 'grid', gap: 20, marginBottom: 28 }}>
                <div>
                    <label style={labelStyle} htmlFor="sp-wiz-currency">
                        {__( 'Currency', 'smartpay' )}
                        <span style={{ color: '#d63638', marginLeft: 3 }}>*</span>
                    </label>
                    <select
                        id="sp-wiz-currency"
                        value={currency}
                        onChange={( e ) => setCurrency( e.target.value )}
                        onFocus={() => setFocusCur( true )}
                        onBlur={() => setFocusCur( false )}
                        style={{ ...inputStyle, ...(focusCur ? focusStyle : {}), cursor: 'pointer' }}
                    >
                        {Object.entries( currencies ).map( ( [code, data] ) => (
                            <option key={code} value={code}>
                                {data.name ? `${data.name} (${code})` : code}
                            </option>
                        ) )}
                    </select>
                    <p style={hintStyle}>{__( 'All payment amounts will be processed in this currency.', 'smartpay' )}</p>
                </div>

                <div>
                    <label style={labelStyle} htmlFor="sp-wiz-bname">
                        {__( 'Business Name', 'smartpay' )}
                    </label>
                    <input
                        id="sp-wiz-bname"
                        type="text"
                        value={businessName}
                        onChange={( e ) => setBusinessName( e.target.value )}
                        onFocus={() => setFocusBiz( true )}
                        onBlur={() => setFocusBiz( false )}
                        placeholder={__( 'e.g. Acme Corp or My Shop', 'smartpay' )}
                        maxLength={200}
                        style={{ ...inputStyle, ...(focusBiz ? focusStyle : {}) }}
                    />
                    <p style={hintStyle}>{__( 'Shown on invoices and email receipts.', 'smartpay' )}</p>
                </div>
            </div>

            {error && (
                <div style={{ background: '#fef2f2', border: '1px solid #fecaca', borderRadius: 6, padding: '10px 14px', marginBottom: 20 }}>
                    <p style={{ color: '#dc2626', fontSize: 12.5, margin: 0, lineHeight: 1.5 }}>{error}</p>
                </div>
            )}

            <div style={footerStyle}>
                <button
                    type="button"
                    onClick={save}
                    disabled={saving}
                    style={{ ...btnPrimary, ...(saving ? { opacity: 0.7, cursor: 'not-allowed' } : {}) }}
                    onMouseOver={( e ) => { if ( ! saving ) e.currentTarget.style.background = '#1a2730' }}
                    onMouseOut={( e )  => { if ( ! saving ) e.currentTarget.style.background = '#1d2327' }}
                >
                    {saving ? (
                        <>
                            <span style={spinnerStyle} />
                            {__( 'Saving…', 'smartpay' )}
                        </>
                    ) : __( 'Save & Continue →', 'smartpay' )}
                </button>
            </div>
        </div>
    )
}

// ── Step 2 ────────────────────────────────────────────────────

function Step2( { onNext } ) {
    const gateways   = options.gateways || {}
    const intUrl     = `${adminUrl}?page=smartpay-integrations`
    const hasGateways = Object.keys( gateways ).length > 0

    return (
        <div style={bodyStyle}>
            <div style={stepBadgeStyle}>02</div>
            <h2 style={headingStyle}>{__( 'Connect a payment gateway', 'smartpay' )}</h2>
            <p style={subStyle}>{__( 'Link a gateway to start accepting real payments. You can configure credentials on the next screen.', 'smartpay' )}</p>

            <div style={{ marginBottom: 28 }}>
                {hasGateways ? (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                        {Object.entries( gateways ).map( ( [slug, label] ) => (
                            <a
                                key={slug}
                                href={intUrl}
                                style={{
                                    display:        'flex',
                                    alignItems:     'center',
                                    justifyContent: 'space-between',
                                    padding:        '14px 16px',
                                    border:         '1px solid #e5e7eb',
                                    borderRadius:   8,
                                    textDecoration: 'none',
                                    background:     '#fff',
                                    transition:     'border-color .15s, box-shadow .15s',
                                }}
                                onMouseOver={( e ) => { e.currentTarget.style.borderColor = '#293c81'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(41,60,129,.08)' }}
                                onMouseOut={( e )  => { e.currentTarget.style.borderColor = '#e5e7eb'; e.currentTarget.style.boxShadow = 'none' }}
                            >
                                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                                    <div style={{ width: 36, height: 36, borderRadius: 8, background: '#f3f4f6', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
                                            <rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>
                                        </svg>
                                    </div>
                                    <span style={{ fontSize: 13.5, fontWeight: 600, color: '#1d2327' }}>{label}</span>
                                </div>
                                <span style={{ fontSize: 12, color: '#293c81', fontWeight: 600 }}>{__( 'Configure →', 'smartpay' )}</span>
                            </a>
                        ) )}
                    </div>
                ) : (
                    <div style={{ padding: '24px', border: '1.5px dashed #d1d5db', borderRadius: 8, textAlign: 'center' }}>
                        <p style={{ fontSize: 13, color: '#6b7280', margin: '0 0 12px' }}>
                            {__( 'No gateways found. Visit Integrations to connect one.', 'smartpay' )}
                        </p>
                        <a href={intUrl} style={{ fontSize: 13, color: '#293c81', fontWeight: 600, textDecoration: 'none' }}>
                            {__( 'Open Integrations →', 'smartpay' )}
                        </a>
                    </div>
                )}
            </div>

            <div style={footerStyle}>
                <button type="button" onClick={onNext} style={btnPrimary}
                    onMouseOver={( e ) => e.currentTarget.style.background = '#1a2730'}
                    onMouseOut={( e )  => e.currentTarget.style.background = '#1d2327'}
                >
                    {__( "I've connected a gateway →", 'smartpay' )}
                </button>
                <button type="button" onClick={onNext} style={btnGhost}>
                    {__( "Skip for now", 'smartpay' )}
                </button>
            </div>
        </div>
    )
}

// ── Step 3 ────────────────────────────────────────────────────

function Step3( { onClose } ) {
    const go = ( href ) => {
        onClose()
        window.location.href = href
    }

    return (
        <div style={{ ...bodyStyle, textAlign: 'center', padding: '48px 40px 40px' }}>
            {/* Success icon */}
            <div style={{
                width: 64, height: 64, borderRadius: '50%',
                background: '#f0fdf4', border: '2px solid #86efac',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                margin: '0 auto 20px',
            }}>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>

            <h2 style={{ ...headingStyle, fontSize: 24, marginBottom: 10 }}>
                {__( "You're ready to accept payments", 'smartpay' )}
            </h2>
            <p style={{ ...subStyle, maxWidth: 380, margin: '0 auto 32px' }}>
                {__( 'Create your first payment form and start collecting payments today.', 'smartpay' )}
            </p>

            <div style={{ display: 'flex', gap: 12, justifyContent: 'center', flexWrap: 'wrap', marginBottom: 24 }}>
                <button
                    type="button"
                    onClick={() => go( `${adminUrl}?page=smartpay#/native-forms` )}
                    style={btnPrimary}
                    onMouseOver={( e ) => e.currentTarget.style.background = '#1a2730'}
                    onMouseOut={( e )  => e.currentTarget.style.background = '#1d2327'}
                >
                    {__( 'Build a Payment Form', 'smartpay' )}
                </button>
            </div>

            <button type="button" onClick={onClose} style={btnGhost}>
                {__( 'Go to Dashboard', 'smartpay' )}
            </button>
        </div>
    )
}

// ── Main wizard ───────────────────────────────────────────────

export function SetupWizard( { isOpen, onClose } ) {
    const [step, setStep] = useState( 1 )

    const handleClose = useCallback( () => {
        localStorage.setItem( WIZARD_KEY, 'done' )
        onClose()
    }, [onClose] )

    useEffect( () => {
        if ( isOpen ) setStep( 1 )
    }, [isOpen] )

    if ( ! isOpen ) return null

    const STEPS = [
        <Step1 key={1} onNext={() => setStep( 2 )} />,
        <Step2 key={2} onNext={() => setStep( 3 )} />,
        <Step3 key={3} onClose={handleClose} />,
    ]

    const progressPct = ( step / 3 ) * 100

    return (
        <div style={{
            position:       'fixed',
            inset:          0,
            background:     'rgba(0,0,0,0.6)',
            zIndex:         100050,
            display:        'flex',
            alignItems:     'center',
            justifyContent: 'center',
            padding:        16,
        }}>
            <div style={{
                background:   '#fff',
                borderRadius: 12,
                boxShadow:    '0 20px 60px rgba(0,0,0,.22)',
                width:        '100%',
                maxWidth:     620,
                maxHeight:    '92vh',
                overflowY:    'auto',
                position:     'relative',
            }}>
                {/* Header */}
                <div style={{
                    padding:        '18px 24px',
                    borderBottom:   '1px solid #f0f0f1',
                    display:        'flex',
                    alignItems:     'center',
                    justifyContent: 'space-between',
                    position:       'sticky',
                    top:            0,
                    background:     '#fff',
                    zIndex:         1,
                }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        {options.logoUrl && (
                            <img src={options.logoUrl} alt="" style={{ width: 30, height: 30, objectFit: 'contain', flexShrink: 0 }} />
                        )}
                        <div>
                            <span style={{ display: 'block', fontSize: 14, fontWeight: 700, color: '#293c81', lineHeight: 1.2, letterSpacing: '-.2px' }}>
                                WPSmartPay
                            </span>
                            <span style={{ display: 'block', fontSize: 11, color: '#9ca3af', fontWeight: 500, marginTop: 1 }}>
                                {__( 'Setup Wizard', 'smartpay' )}
                            </span>
                        </div>
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 6 }}>
                            <span style={{ fontSize: 11, color: '#9ca3af', fontWeight: 500 }}>
                                {__( 'Step', 'smartpay' )} {step} {__( 'of 3', 'smartpay' )}
                            </span>
                            <StepDots current={step} total={3} />
                        </div>
                        <button
                            type="button"
                            onClick={handleClose}
                            aria-label={__( 'Skip setup', 'smartpay' )}
                            style={{
                                width: 32, height: 32, borderRadius: 6,
                                background: 'none', border: '1px solid #e5e7eb',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                cursor: 'pointer', color: '#6b7280', flexShrink: 0,
                            }}
                            onMouseOver={( e ) => { e.currentTarget.style.background = '#f9fafb'; e.currentTarget.style.borderColor = '#d1d5db' }}
                            onMouseOut={( e )  => { e.currentTarget.style.background = 'none'; e.currentTarget.style.borderColor = '#e5e7eb' }}
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {/* Progress bar */}
                <div style={{ height: 3, background: '#f3f4f6' }}>
                    <div style={{
                        height:     '100%',
                        width:      `${progressPct}%`,
                        background: 'linear-gradient(90deg, #293c81 0%, #3d52a3 100%)',
                        transition: 'width .35s ease',
                    }} />
                </div>

                {/* Step content */}
                {STEPS[step - 1]}
            </div>
        </div>
    )
}

// ── Shared styles ─────────────────────────────────────────────

const bodyStyle = {
    padding: '36px 40px 32px',
}

const stepBadgeStyle = {
    display:      'inline-flex',
    alignItems:   'center',
    justifyContent: 'center',
    width:        28,
    height:       28,
    borderRadius: 6,
    background:   '#eef0f8',
    color:        '#293c81',
    fontSize:     11,
    fontWeight:   700,
    letterSpacing: '.5px',
    marginBottom: 14,
}

const headingStyle = {
    fontSize:      22,
    fontWeight:    700,
    color:         '#1d2327',
    marginBottom:  8,
    lineHeight:    1.25,
    letterSpacing: '-.3px',
}

const subStyle = {
    fontSize:     13.5,
    color:        '#6b7280',
    marginBottom: 28,
    lineHeight:   1.65,
}

const labelStyle = {
    display:      'block',
    fontSize:     12.5,
    fontWeight:   600,
    color:        '#374151',
    marginBottom: 7,
}

const inputStyle = {
    display:      'block',
    width:        '100%',
    height:       42,
    padding:      '0 12px',
    fontSize:     13.5,
    border:       '1.5px solid #d1d5db',
    borderRadius: 6,
    color:        '#1d2327',
    background:   '#fff',
    boxSizing:    'border-box',
    outline:      'none',
    transition:   'border-color .15s, box-shadow .15s',
    appearance:   'auto',
}

const focusStyle = {
    borderColor: '#293c81',
    boxShadow:   '0 0 0 3px rgba(41,60,129,.1)',
}

const hintStyle = {
    fontSize:   11.5,
    color:      '#9ca3af',
    marginTop:  5,
    lineHeight: 1.5,
}

const footerStyle = {
    display:    'flex',
    alignItems: 'center',
    gap:        12,
    flexWrap:   'wrap',
}

const btnPrimary = {
    display:        'inline-flex',
    alignItems:     'center',
    justifyContent: 'center',
    gap:            7,
    background:     '#1d2327',
    color:          '#fff',
    border:         'none',
    borderRadius:   7,
    padding:        '11px 22px',
    fontSize:       13.5,
    fontWeight:     600,
    cursor:         'pointer',
    lineHeight:     1,
    textDecoration: 'none',
    transition:     'background .15s',
}

const btnGhost = {
    background:  'none',
    border:      'none',
    cursor:      'pointer',
    fontSize:    12.5,
    color:       '#9ca3af',
    fontWeight:  500,
    padding:     '11px 4px',
    lineHeight:  1,
    textDecoration: 'none',
    transition:  'color .15s',
}

const spinnerStyle = {
    display:     'inline-block',
    width:       12,
    height:      12,
    border:      '2px solid rgba(255,255,255,.3)',
    borderTop:   '2px solid #fff',
    borderRadius: '50%',
    animation:   'sp-spin .7s linear infinite',
    marginRight: 4,
}

export default SetupWizard
