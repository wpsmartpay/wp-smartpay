import { useState, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { HelpCircle } from 'lucide-react'
import { HelpDrawer } from './HelpDrawer'

const BASE_TITLE = 'SmartPay'

export function Header({ title, subtitle }) {
    const [helpOpen, setHelpOpen] = useState(false)

    useEffect(() => {
        document.title = title ? `${title} — ${BASE_TITLE}` : BASE_TITLE
    }, [title])

    return (
        <>
            {/* Compact nav bar — logo left, help right */}
            <div className="smartpay-page-header">
                <div className="smartpay-page-header__inner">
                    <div className="smartpay-page-header__logo">
                        <img src={smartpay.logo} alt="SmartPay" />
                    </div>
                    <div className="smartpay-page-header__actions">
                        <button
                            type="button"
                            className="smartpay-page-header__help-btn"
                            onClick={() => setHelpOpen(true)}
                            title={__('Help & Documentation', 'smartpay')}
                            aria-label={__('Open help documentation', 'smartpay')}
                        >
                            <HelpCircle style={{ width: 14, height: 14, opacity: 0.7 }} />
                            {__('Help', 'smartpay')}
                        </button>
                    </div>
                </div>
            </div>

            <HelpDrawer open={helpOpen} onOpenChange={setHelpOpen} />
        </>
    )
}
