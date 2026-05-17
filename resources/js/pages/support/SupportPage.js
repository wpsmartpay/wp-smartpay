import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Header } from '../../components/header'
import { SystemInfo } from './SystemInfo'
import { DebugLog } from './DebugLog'

// ── Icons ─────────────────────────────────────────────────────

const BookIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
    </svg>
)

const VideoIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
        <path strokeLinecap="round" strokeLinejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
    </svg>
)

const ChatIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
        <path strokeLinecap="round" strokeLinejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
    </svg>
)

const ChangelogIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
        <path strokeLinecap="round" strokeLinejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
    </svg>
)

const CodeIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
        <path strokeLinecap="round" strokeLinejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
    </svg>
)

const StarIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.75">
        <path strokeLinecap="round" strokeLinejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
    </svg>
)

const ExternalIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
    </svg>
)

// ── Data ──────────────────────────────────────────────────────

const TABS = [
    { key: 'support',  label: __('Support & Docs', 'smartpay') },
    { key: 'sysinfo',  label: __('System Info', 'smartpay') },
    { key: 'debuglog', label: __('Debug Log', 'smartpay') },
]

const DOC_LINKS = [
    {
        icon: <BookIcon />,
        title: __('Documentation', 'smartpay'),
        desc:  __('Setup guides, shortcodes, and developer references.', 'smartpay'),
        url:   'https://docs.wpsmartpay.com/',
    },
    {
        icon: <VideoIcon />,
        title: __('Video Tutorials', 'smartpay'),
        desc:  __('Watch step-by-step overviews for common workflows.', 'smartpay'),
        url:   'https://www.youtube.com/watch?v=PdqA7XNH60Q',
    },
    {
        icon: <ChatIcon />,
        title: __('Contact Support', 'smartpay'),
        desc:  __('Open a ticket and get help from the SmartPay team.', 'smartpay'),
        url:   'https://wpsmartpay.com/support/',
    },
    {
        icon: <ChangelogIcon />,
        title: __('Changelog', 'smartpay'),
        desc:  __("What's new — release notes and version history.", 'smartpay'),
        url:   'https://wpsmartpay.com/changelog/',
    },
    {
        icon: <CodeIcon />,
        title: __('Developer Docs', 'smartpay'),
        desc:  __('Hooks, REST API, and integration patterns for developers.', 'smartpay'),
        url:   'https://docs.wpsmartpay.com/en/category/developer',
    },
    {
        icon: <StarIcon />,
        title: __('Leave a Review', 'smartpay'),
        desc:  __('Enjoying SmartPay? Share your experience on WordPress.org.', 'smartpay'),
        url:   'https://wordpress.org/support/plugin/wp-smartpay/reviews/',
    },
]

const QUICK_LINKS = [
    { href: 'https://wpsmartpay.com/support/',                               icon: <ChatIcon />,     label: __('Open a Support Ticket', 'smartpay') },
    { href: 'https://docs.wpsmartpay.com/',                                   icon: <BookIcon />,     label: __('Search Documentation', 'smartpay') },
    { href: 'https://wordpress.org/support/plugin/wp-smartpay/reviews/',      icon: <StarIcon />,     label: __('Leave a Review', 'smartpay') },
]

// ── Components ────────────────────────────────────────────────

function DocLinkRow({ link }) {
    return (
        <a href={link.url} target="_blank" rel="noopener noreferrer" className="sp-support-link">
            <div className="sp-support-link__icon">{link.icon}</div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <div className="sp-support-link__title">{link.title}</div>
                <div className="sp-support-link__desc">{link.desc}</div>
            </div>
            <div className="sp-support-link__arrow"><ExternalIcon /></div>
        </a>
    )
}

function SupportTab() {
    const { version } = window.smartpaySupport || {}

    return (
        <div className="sp-detail-grid">
            {/* Main column — resource links */}
            <div>
                <div className="sp-detail-card">
                    <div className="sp-detail-card__header">
                        <span className="sp-detail-card__title">{__('Resources', 'smartpay')}</span>
                    </div>
                    <div className="sp-detail-card__body" style={{ padding: 0 }}>
                        {DOC_LINKS.map((link) => (
                            <DocLinkRow key={link.url} link={link} />
                        ))}
                    </div>
                </div>
            </div>

            {/* Sidebar — plugin info + quick links */}
            <div className="sp-detail-sidebar">
                {version && (
                    <div className="sp-detail-card">
                        <div className="sp-detail-card__header">
                            <span className="sp-detail-card__title">{__('Plugin', 'smartpay')}</span>
                        </div>
                        <div className="sp-detail-card__body">
                            <table className="sp-kv-table">
                                <tbody>
                                    <tr>
                                        <td>{__('Version', 'smartpay')}</td>
                                        <td><span className="sp-version-tag">v{version}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{__('Status', 'smartpay')}</td>
                                        <td><span className="sp-badge sp-badge--active">{__('Active', 'smartpay')}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                            <a
                                href="https://wpsmartpay.com/changelog/"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="sp-btn sp-btn--outline"
                                style={{ marginTop: 12 }}
                            >
                                {__('View Changelog', 'smartpay')} →
                            </a>
                        </div>
                    </div>
                )}

                <div className="sp-detail-card">
                    <div className="sp-detail-card__header">
                        <span className="sp-detail-card__title">{__('Quick Links', 'smartpay')}</span>
                    </div>
                    <div className="sp-detail-card__body" style={{ padding: '8px 0' }}>
                        {QUICK_LINKS.map(({ href, icon, label }) => (
                            <a key={href} href={href} target="_blank" rel="noopener noreferrer" className="sp-quick-links__item">
                                <span style={{ color: 'var(--sp-brand)', display: 'flex', flexShrink: 0 }}>{icon}</span>
                                {label}
                            </a>
                        ))}
                    </div>
                </div>
            </div>
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

                {tab === 'support'  && <SupportTab />}
                {tab === 'sysinfo'  && <SystemInfo />}
                {tab === 'debuglog' && <DebugLog />}
            </div>
        </>
    )
}
