import { __ } from '@wordpress/i18n'

const { version } = window.smartpay

const FOOTER_LINKS = [
    { label: __('Support', 'smartpay'),   url: 'https://wpsmartpay.com/support/' },
    { label: __('Docs', 'smartpay'),      url: 'https://wpsmartpay.com/docs/' },
    { label: __('Community', 'smartpay'), url: 'https://wpsmartpay.com/community/' },
]

const RATE_URL = 'https://wordpress.org/plugins/smartpay/#reviews'

export function AdminFooter() {
    return (
        <footer
            role="contentinfo"
            className="mt-8 border-t border-border bg-background"
        >
            <div className="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">

                {/* Left: branding + version */}
                <div className="flex items-center gap-3 text-sm text-muted-foreground">
                    <span>
                        {__('Made with', 'smartpay')}
                        {' '}
                        <span
                            className="text-red-400"
                            aria-label={__('love', 'smartpay')}
                        >
                            ♥
                        </span>
                        {' '}
                        {__('by the WP SmartPay Team', 'smartpay')}
                    </span>
                    <span className="hidden sm:inline text-border" aria-hidden="true">·</span>
                    <span className="hidden sm:inline text-xs font-mono text-muted-foreground/60">
                        v{version}
                    </span>
                </div>

                {/* Right: links + rate */}
                <div className="flex items-center gap-4 text-sm">
                    <nav
                        className="flex items-center gap-3"
                        aria-label={__('Footer navigation', 'smartpay')}
                    >
                        {FOOTER_LINKS.map(({ label, url }) => (
                            <a
                                key={label}
                                href={url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-muted-foreground hover:text-foreground transition-colors no-underline text-sm"
                            >
                                {label}
                            </a>
                        ))}
                    </nav>

                    <span className="text-border" aria-hidden="true">·</span>

                    <a
                        href={RATE_URL}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-amber-400 hover:text-amber-500 transition-colors no-underline text-sm tracking-tight"
                        aria-label={__('Rate WP SmartPay on WordPress.org', 'smartpay')}
                    >
                        ★★★★★
                    </a>
                </div>

            </div>
        </footer>
    )
}
