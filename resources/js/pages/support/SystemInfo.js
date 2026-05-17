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
        <button type="button" onClick={copy} className="sp-btn sp-btn--outline" style={{ flexShrink: 0, fontSize: 12 }}>
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

const ChevronIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="14" height="14"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2.5"
        strokeLinecap="round"
        strokeLinejoin="round"
        className="sp-accordion-btn__chevron"
    >
        <path d="m6 9 6 6 6-6"/>
    </svg>
)

export function SystemInfo() {
    const { systemInfo } = window.smartpaySupport || {}
    const [open, setOpen] = useState({ wordpress: true, server: true, smartpay: true, plugins: false })

    if (!systemInfo) return null

    const toggle = (key) => setOpen(prev => ({ ...prev, [key]: !prev[key] }))

    return (
        <div className="sp-detail-card">
            <div className="sp-detail-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <div>
                    <span className="sp-detail-card__title">{__('System Information', 'smartpay')}</span>
                    <p style={{ color: 'var(--sp-text-muted)', fontSize: '12.5px', margin: '4px 0 0' }}>
                        {__('Share this with support when reporting an issue.', 'smartpay')}
                    </p>
                </div>
                <CopyButton text={buildCopyText(systemInfo)} />
            </div>

            <div className="sp-detail-card__body" style={{ padding: 0 }}>
                {Object.entries(systemInfo).map(([key, rows]) => (
                    <div key={key} style={{ borderBottom: '1px solid var(--sp-border)' }}>
                        <button
                            type="button"
                            className={`sp-accordion-btn${open[key] ? ' sp-accordion-btn--open' : ''}`}
                            onClick={() => toggle(key)}
                        >
                            <span style={{ flex: 1 }}>{SECTION_LABELS[key] ?? key}</span>
                            <ChevronIcon />
                        </button>

                        {open[key] && (
                            <div style={{ padding: '0 20px 4px' }}>
                                <table className="sp-kv-table">
                                    <tbody>
                                        {key === 'plugins'
                                            ? rows.map((plugin, i) => (
                                                <tr key={i}>
                                                    <td>{plugin.name}</td>
                                                    <td>{plugin.version}</td>
                                                </tr>
                                            ))
                                            : rows.map((row, i) => (
                                                <tr key={i}>
                                                    <td>{row.label}</td>
                                                    <td>{row.value}</td>
                                                </tr>
                                            ))
                                        }
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </div>
    )
}
