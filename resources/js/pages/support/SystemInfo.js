import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

const SECTION_LABELS = {
    wordpress: __('WordPress', 'smartpay'),
    server:    __('Server', 'smartpay'),
    smartpay:  __('SmartPay', 'smartpay'),
    plugins:   __('Active Plugins', 'smartpay'),
}

function CopyButton({ text }) {
    const [copied, setCopied] = useState(false)

    const copy = () => {
        navigator.clipboard.writeText(text).then(() => {
            setCopied(true)
            setTimeout(() => setCopied(false), 2000)
        })
    }

    return (
        <button type="button" onClick={copy} style={styles.copyBtn}>
            {copied ? __('Copied!', 'smartpay') : __('Copy', 'smartpay')}
        </button>
    )
}

function buildCopyText(systemInfo) {
    const lines = []
    Object.entries(systemInfo).forEach(([section, rows]) => {
        lines.push(`### ${SECTION_LABELS[section] ?? section} ###`)
        if (section === 'plugins') {
            rows.forEach(p => lines.push(`${p.name}: ${p.version}`))
        } else {
            rows.forEach(r => lines.push(`${r.label}: ${r.value}`))
        }
        lines.push('')
    })
    return lines.join('\n')
}

export function SystemInfo() {
    const { systemInfo } = window.smartpaySupport || {}
    const [open, setOpen] = useState({ wordpress: true, server: true, smartpay: true, plugins: false })

    if (!systemInfo) return null

    const toggle = (key) => setOpen(prev => ({ ...prev, [key]: !prev[key] }))

    return (
        <div style={styles.card}>
            <div style={styles.cardHeader}>
                <div>
                    <h3 style={styles.cardTitle}>{__('System Information', 'smartpay')}</h3>
                    <p style={styles.cardDesc}>{__('Share this with support when reporting an issue.', 'smartpay')}</p>
                </div>
                <CopyButton text={buildCopyText(systemInfo)} />
            </div>

            {Object.entries(systemInfo).map(([key, rows]) => (
                <div key={key} style={styles.section}>
                    <button
                        type="button"
                        style={styles.sectionToggle}
                        onClick={() => toggle(key)}
                    >
                        <span style={styles.sectionLabel}>{SECTION_LABELS[key] ?? key}</span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="14" height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2.5"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            style={{ transform: open[key] ? 'rotate(180deg)' : 'none', transition: 'transform .2s', flexShrink: 0 }}
                        >
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    {open[key] && (
                        key === 'plugins' ? (
                            <table style={styles.table}>
                                <tbody>
                                    {rows.map((plugin, i) => (
                                        <tr key={i} style={i % 2 === 0 ? styles.rowEven : styles.rowOdd}>
                                            <td style={styles.cellLabel}>{plugin.name}</td>
                                            <td style={styles.cellValue}>{plugin.version}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <table style={styles.table}>
                                <tbody>
                                    {rows.map((row, i) => (
                                        <tr key={i} style={i % 2 === 0 ? styles.rowEven : styles.rowOdd}>
                                            <td style={styles.cellLabel}>{row.label}</td>
                                            <td style={styles.cellValue}>{row.value}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )
                    )}
                </div>
            ))}
        </div>
    )
}

const styles = {
    card: {
        background: '#fff',
        border: '1px solid #e5e7eb',
        borderRadius: '12px',
        overflow: 'hidden',
    },
    cardHeader: {
        alignItems: 'flex-start',
        borderBottom: '1px solid #f3f4f6',
        display: 'flex',
        justifyContent: 'space-between',
        padding: '20px 24px 16px',
    },
    cardTitle: {
        color: '#111827',
        fontSize: '15px',
        fontWeight: '700',
        margin: '0 0 3px',
    },
    cardDesc: {
        color: '#6b7280',
        fontSize: '12.5px',
        margin: '0',
    },
    copyBtn: {
        alignItems: 'center',
        background: '#fff',
        border: '1px solid #e5e7eb',
        borderRadius: '7px',
        color: '#374151',
        cursor: 'pointer',
        display: 'inline-flex',
        flexShrink: '0',
        fontSize: '12px',
        fontWeight: '500',
        marginTop: '2px',
        padding: '6px 14px',
        transition: 'background .15s',
        whiteSpace: 'nowrap',
    },
    section: {
        borderBottom: '1px solid #f3f4f6',
    },
    sectionToggle: {
        alignItems: 'center',
        background: 'none',
        border: 'none',
        borderBottom: 'none',
        color: '#374151',
        cursor: 'pointer',
        display: 'flex',
        fontSize: '13px',
        fontWeight: '600',
        gap: '8px',
        justifyContent: 'space-between',
        padding: '13px 24px',
        width: '100%',
    },
    sectionLabel: { flex: 1, textAlign: 'left' },
    table: {
        borderCollapse: 'collapse',
        width: '100%',
    },
    rowEven: { background: '#fff' },
    rowOdd:  { background: '#f9fafb' },
    cellLabel: {
        color: '#374151',
        fontSize: '12.5px',
        fontWeight: '500',
        padding: '8px 24px',
        width: '220px',
    },
    cellValue: {
        color: '#111827',
        fontSize: '12.5px',
        fontFamily: 'monospace',
        padding: '8px 24px 8px 0',
        wordBreak: 'break-all',
    },
}
