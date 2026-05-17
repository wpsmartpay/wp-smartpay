import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

function CopyButton({ text }) {
    const [copied, setCopied] = useState(false)

    const copy = () => {
        navigator.clipboard.writeText(text).then(() => {
            setCopied(true)
            setTimeout(() => setCopied(false), 2000)
        })
    }

    return (
        <button type="button" onClick={copy} className="sp-btn sp-btn--outline" style={{ fontSize: 12 }}>
            {copied ? __('Copied!', 'smartpay') : __('Copy', 'smartpay')}
        </button>
    )
}

export function DebugLog() {
    const { debugLog, nonce, restUrl } = window.smartpaySupport || {}
    const [log, setLog] = useState(debugLog || '')
    const [clearing, setClearing] = useState(false)
    const [cleared, setCleared] = useState(false)

    const clearLog = async () => {
        setClearing(true)
        try {
            const url = new URL(`${restUrl}/support/debug-log/clear`)
            await fetch(url.toString(), {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce,
                    'Content-Type': 'application/json',
                },
            })
            setLog('')
            setCleared(true)
            setTimeout(() => setCleared(false), 2000)
        } catch (e) {
            // silent
        } finally {
            setClearing(false)
        }
    }

    return (
        <div className="sp-detail-card">
            <div className="sp-detail-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <div>
                    <span className="sp-detail-card__title">{__('Debug Log', 'smartpay')}</span>
                    <p style={{ color: 'var(--sp-text-muted)', fontSize: '12.5px', margin: '4px 0 0' }}>{__('Recent log entries from the SmartPay debug log file.', 'smartpay')}</p>
                </div>
                <div style={{ display: 'flex', gap: 8, flexShrink: 0, marginTop: 2 }}>
                    {log && <CopyButton text={log} />}
                    <button
                        type="button"
                        onClick={clearLog}
                        disabled={clearing || !log}
                        className="sp-btn sp-btn--outline"
                        style={{
                            fontSize: 12,
                            ...(log ? { color: '#dc2626', borderColor: '#fecaca' } : { opacity: 0.45, cursor: 'default' }),
                        }}
                    >
                        {cleared ? __('Cleared!', 'smartpay') : clearing ? __('Clearing…', 'smartpay') : __('Clear Log', 'smartpay')}
                    </button>
                </div>
            </div>

            <div className="sp-detail-card__body" style={{ padding: 0 }}>
                {log ? (
                    <pre style={{
                        background: '#111827',
                        color: '#d1fae5',
                        fontFamily: 'monospace',
                        fontSize: '11.5px',
                        lineHeight: '1.65',
                        margin: '0',
                        maxHeight: '480px',
                        overflowY: 'auto',
                        padding: '20px 24px',
                        whiteSpace: 'pre-wrap',
                        wordBreak: 'break-all',
                    }}>{log}</pre>
                ) : (
                    <div style={{ alignItems: 'center', display: 'flex', flexDirection: 'column', gap: 8, padding: '48px 24px', textAlign: 'center' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#d1d5db" strokeWidth="1.5">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p style={{ color: '#374151', fontSize: '13.5px', fontWeight: 600, margin: 0 }}>{__('Debug log is empty.', 'smartpay')}</p>
                        <p style={{ color: '#9ca3af', fontSize: '12.5px', margin: 0 }}>{__('Enable WP_DEBUG_LOG in wp-config.php to capture log entries.', 'smartpay')}</p>
                    </div>
                )}
            </div>
        </div>
    )
}
