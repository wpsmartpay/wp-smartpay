import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Header } from '../../components/header'
import { SystemInfo } from './SystemInfo'
import { DebugLog } from './DebugLog'

const TABS = [
    { key: 'support', label: __('Support & Docs', 'smartpay') },
    { key: 'debug',   label: __('Debug', 'smartpay') },
]

const DOC_LINKS = [
    {
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        ),
        title: __('Documentation', 'smartpay'),
        desc:  __('Setup guides, shortcodes, and developer references.', 'smartpay'),
        url:   'https://docs.wpsmartpay.com/',
    },
    {
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        ),
        title: __('Video Tutorials', 'smartpay'),
        desc:  __('Watch step-by-step overviews for common workflows.', 'smartpay'),
        url:   'https://www.youtube.com/watch?v=PdqA7XNH60Q',
    },
    {
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
        ),
        title: __('Contact Support', 'smartpay'),
        desc:  __('Open a ticket and get help from the SmartPay team.', 'smartpay'),
        url:   'https://wpsmartpay.com/support/',
    },
    {
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
            </svg>
        ),
        title: __('Changelog', 'smartpay'),
        desc:  __("What's new — release notes and version history.", 'smartpay'),
        url:   'https://wpsmartpay.com/changelog/',
    },
    {
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
            </svg>
        ),
        title: __('Developer Docs', 'smartpay'),
        desc:  __('Hooks, REST API, and integration patterns for developers.', 'smartpay'),
        url:   'https://docs.wpsmartpay.com/en/category/developer',
    },
    {
        icon: (
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
                <path strokeLinecap="round" strokeLinejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
            </svg>
        ),
        title: __('Leave a Review', 'smartpay'),
        desc:  __('Enjoying SmartPay? Share your experience on WordPress.org.', 'smartpay'),
        url:   'https://wordpress.org/support/plugin/wp-smartpay/reviews/',
    },
]

function SupportTab() {
    const { version } = window.smartpaySupport || {}

    return (
        <div>
            <div style={{ marginBottom: 20 }}>
                <h2 style={{ color: 'var(--sp-text)', fontSize: 15, fontWeight: 700, margin: '0 0 4px' }}>{__('Resources', 'smartpay')}</h2>
                <p style={{ color: 'var(--sp-text-muted)', fontSize: 13, margin: 0 }}>{__('Everything you need to get help, learn more, or contribute.', 'smartpay')}</p>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 12 }}>
                {DOC_LINKS.map((link) => (
                    <a
                        key={link.url}
                        href={link.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="sp-detail-card"
                        style={{ display: 'flex', gap: 14, padding: '16px 18px', textDecoration: 'none', color: 'inherit', marginBottom: 0 }}
                        onMouseEnter={e => { e.currentTarget.style.borderColor = 'var(--sp-brand)' }}
                        onMouseLeave={e => { e.currentTarget.style.borderColor = 'var(--sp-border)' }}
                    >
                        <div style={{ color: 'var(--sp-brand)', flexShrink: 0, marginTop: 1 }}>{link.icon}</div>
                        <div>
                            <div style={{ color: 'var(--sp-text)', fontSize: '13.5px', fontWeight: 600, marginBottom: 3 }}>{link.title}</div>
                            <div style={{ color: 'var(--sp-text-muted)', fontSize: '12.5px', lineHeight: 1.5 }}>{link.desc}</div>
                        </div>
                    </a>
                ))}
            </div>

            {version && (
                <div className="sp-detail-card" style={{ display: 'flex', alignItems: 'center', gap: 10, marginTop: 16, padding: '12px 18px' }}>
                    <span style={{ background: 'var(--sp-brand-light)', borderRadius: 'var(--sp-radius-sm)', color: 'var(--sp-brand)', fontSize: 11, fontWeight: 700, letterSpacing: '.05em', padding: '3px 8px', textTransform: 'uppercase' }}>v{version}</span>
                    <span style={{ color: 'var(--sp-text)', flex: 1, fontSize: 13 }}>{__('SmartPay is up to date.', 'smartpay')}</span>
                    <a href="https://wpsmartpay.com/changelog/" target="_blank" rel="noopener noreferrer" style={{ color: 'var(--sp-brand)', fontSize: '12.5px', textDecoration: 'none' }}>
                        {__("What's new →", 'smartpay')}
                    </a>
                </div>
            )}
        </div>
    )
}

export function SupportPage() {
    const [tab, setTab] = useState('support')

    return (
        <>
            <Header
                title={__('Support', 'smartpay')}
                subtitle={__('Documentation, debug tools, and system information.', 'smartpay')}
            />

            <div className="sp-layout">
                <div className="sp-filter-tabs" style={{ marginBottom: 24 }}>
                    {TABS.map((t) => (
                        <button
                            key={t.key}
                            type="button"
                            onClick={() => setTab(t.key)}
                            className={'sp-filter-tab' + (tab === t.key ? ' sp-filter-tab--active' : '')}
                        >
                            {t.label}
                        </button>
                    ))}
                </div>

                {tab === 'support' && <SupportTab />}
                {tab === 'debug'   && <DebugTab />}
            </div>
        </>
    )
}

function DebugTab() {
    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
            <SystemInfo />
            <DebugLog />
        </div>
    )
}
