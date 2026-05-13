<?php defined('ABSPATH') || exit; ?>
<div class="smartpay" style="display: block !important;">
    <div class="wrap" style="display:none">
        <h2></h2>
    </div>
    <div class="smartpay-page-header">
        <div class="smartpay-page-header__inner">
            <div class="smartpay-page-header__text">
                <h2 class="smartpay-page-header__title"><?php esc_html_e( 'Forms (Legacy)', 'smartpay' ); ?></h2>
                <p class="smartpay-page-header__subtitle"><?php esc_html_e( 'Build and manage your payment forms', 'smartpay' ); ?></p>
            </div>
            <div class="smartpay-page-header__actions">
                <div class="smartpay-page-header__logo">
                    <img src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo.png' ); ?>" alt="SmartPay" />
                </div>
            </div>
        </div>
    </div>
    <div id="smartpay-form"></div>
</div>
