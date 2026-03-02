export function Header({ title, subtitle }) {
    return (
        <div className="smartpay-page-header">
            <div className="smartpay-page-header__inner">
                <div className="smartpay-page-header__text">
                    <h2 className="smartpay-page-header__title">{title}</h2>
                    {subtitle && (
                        <p className="smartpay-page-header__subtitle">{subtitle}</p>
                    )}
                </div>
                <div className="smartpay-page-header__logo">
                    <img src={smartpay.logo} alt="SmartPay Logo" />
                </div>
            </div>
        </div>
    )
}
