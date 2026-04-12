import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { HelpCircle } from 'lucide-react'
import { HelpDrawer } from './HelpDrawer'

export function Header({ title, subtitle }) {
    const [helpOpen, setHelpOpen] = useState(false)

    return (
        <>
            <div className="smartpay-page-header">
                <div className="smartpay-page-header__inner">
                    <div className="smartpay-page-header__text">
                        <h2 className="smartpay-page-header__title">{title}</h2>
                        {subtitle && (
                            <p className="smartpay-page-header__subtitle">{subtitle}</p>
                        )}
                    </div>
                    <div className="smartpay-page-header__actions" style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <button
                            type="button"
                            onClick={() => setHelpOpen(true)}
                            title={__('Help & Documentation', 'smartpay')}
                            aria-label={__('Open help documentation', 'smartpay')}
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: '6px',
                                background: 'none',
                                border: '1px solid var(--wp-components-color-border, #ddd)',
                                borderRadius: '6px',
                                padding: '6px 12px',
                                fontSize: '13px',
                                color: 'var(--wp-components-color-foreground, #1e1e1e)',
                                cursor: 'pointer',
                                lineHeight: 1,
                                transition: 'background 0.15s, border-color 0.15s',
                            }}
                            onMouseEnter={e => { e.currentTarget.style.background = 'var(--wp-components-color-background-subtle, #f6f7f7)' }}
                            onMouseLeave={e => { e.currentTarget.style.background = 'none' }}
                        >
                            <HelpCircle style={{ width: '15px', height: '15px', opacity: 0.7 }} />
                            {__('Help', 'smartpay')}
                        </button>
                        <div className="smartpay-page-header__logo">
                            <img src={smartpay.logo} alt="SmartPay Logo" />
                        </div>
                    </div>
                </div>
            </div>

            <HelpDrawer open={helpOpen} onOpenChange={setHelpOpen} />
        </>
    )
}
