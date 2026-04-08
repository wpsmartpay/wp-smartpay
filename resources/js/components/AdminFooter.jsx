import { __ } from '@wordpress/i18n'

const { version } = window.smartpay

const FOOTER_LINKS = [
    {
        label: __('Support', 'smartpay'),
        url: 'https://wpsmartpay.com/support/',
    },
    {
        label: __('Docs', 'smartpay'),
        url: 'https://wpsmartpay.com/docs/',
    },
    {
        label: __('Community', 'smartpay'),
        url: 'https://wpsmartpay.com/community/',
    },
]

const RATE_URL = 'https://wordpress.org/plugins/smartpay/#reviews'

export function AdminFooter() {
    return (
        <footer className="smartpay-admin-footer" role="contentinfo">
            <div className="smartpay-admin-footer__inner">

                {/* Centered branding + links */}
                <div className="smartpay-admin-footer__center">
                    <p className="smartpay-admin-footer__made-with">
                        {__('Made with', 'smartpay')}
                        &nbsp;
                        <span className="smartpay-admin-footer__heart" aria-label={__('love', 'smartpay')}>
                            ♥
                        </span>
                        &nbsp;
                        {__('by the WP SmartPay Team', 'smartpay')}
                    </p>

                    <nav
                        className="smartpay-admin-footer__links"
                        aria-label={__('Footer navigation', 'smartpay')}
                    >
                        {FOOTER_LINKS.map(({ label, url }, index) => (
                            <span key={label} className="smartpay-admin-footer__link-item">
                                {index > 0 && (
                                    <span className="smartpay-admin-footer__sep" aria-hidden="true">
                                        /
                                    </span>
                                )}
                                <a
                                    href={url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="smartpay-admin-footer__link"
                                >
                                    {label}
                                </a>
                            </span>
                        ))}
                    </nav>
                </div>

                {/* Bottom bar: rate us + version */}
                <div className="smartpay-admin-footer__bar">
                    <p className="smartpay-admin-footer__rate">
                        {__('Please rate', 'smartpay')}
                        &nbsp;
                        <strong>{__('WP SmartPay', 'smartpay')}</strong>
                        &nbsp;
                        <a
                            href={RATE_URL}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="smartpay-admin-footer__stars"
                            aria-label={__('Rate WP SmartPay on WordPress.org', 'smartpay')}
                        >
                            ★★★★★
                        </a>
                        &nbsp;
                        {__('on', 'smartpay')}
                        &nbsp;
                        <a
                            href={RATE_URL}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="smartpay-admin-footer__link"
                        >
                            {__('WordPress.org', 'smartpay')}
                        </a>
                        &nbsp;
                        {__('to help us spread the word.', 'smartpay')}
                    </p>
                    <p className="smartpay-admin-footer__version">
                        {/* translators: %s is the plugin version number */}
                        {__('Version', 'smartpay')} {version}
                    </p>
                </div>

            </div>
        </footer>
    )
}
