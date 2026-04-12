import { __ } from '@wordpress/i18n'

const { version } = window.smartpay

const RATE_URL = 'https://wordpress.org/support/plugin/smartpay/reviews/#new-post'

const FOOTER_LINKS = [
    { label: __( 'Support', 'smartpay' ),   url: 'https://wpsmartpay.com/support/' },
    { label: __( 'Docs', 'smartpay' ),      url: 'https://docs.wpsmartpay.com/en/category/wpsmartpay' },
    { label: __( 'Community', 'smartpay' ), url: 'https://wpsmartpay.com/community/' },
]

export function AdminFooter() {
    return (
        <footer
            role="contentinfo"
            className="mt-8 border-t border-border bg-background"
        >
            <div className="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">

                {/* Left: rating prompt */}
                <p className="text-sm text-muted-foreground m-0">
                    {__( 'If you like', 'smartpay' )}{' '}
                    <strong className="text-foreground font-medium">WP SmartPay</strong>{' '}
                    {__( 'please leave us a', 'smartpay' )}{' '}
                    <a
                        href={RATE_URL}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-amber-400 hover:text-amber-500 transition-colors no-underline tracking-tight"
                        aria-label={__( 'Rate WP SmartPay on WordPress.org', 'smartpay' )}
                    >
                        ★★★★★
                    </a>{' '}
                    {__( 'rating. A huge thanks in advance!', 'smartpay' )}
                </p>

                {/* Right: nav links + version */}
                <div className="flex items-center gap-4 text-sm flex-shrink-0">
                    <nav
                        className="flex items-center gap-3"
                        aria-label={__( 'Footer navigation', 'smartpay' )}
                    >
                        {FOOTER_LINKS.map( ( { label, url } ) => (
                            <a
                                key={label}
                                href={url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-muted-foreground hover:text-foreground transition-colors no-underline text-sm"
                            >
                                {label}
                            </a>
                        ) )}
                    </nav>

                    <span className="text-border" aria-hidden="true">·</span>

                    <span className="text-xs font-mono text-muted-foreground/60">
                        v{version}
                    </span>
                </div>

            </div>
        </footer>
    )
}
