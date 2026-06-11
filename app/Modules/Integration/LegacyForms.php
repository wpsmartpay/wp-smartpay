<?php

namespace SmartPay\Modules\Integration;

defined('ABSPATH') || exit;

use SmartPay\Foundation\Integration;

class LegacyForms extends Integration
{
    public static function config(): array
    {
        return [
            'name'       => __( 'Legacy Forms', 'smartpay' ),
            'excerpt'    => __( 'Enable the legacy form builder for forms created before the native form builder.', 'smartpay' ),
            'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/legacy-forms.png',
            'manager'    => self::class,
            'type'       => 'free',
            'categories' => [ 'Core' ],
        ];
    }

    public function boot(): void
    {
        // Menu registration is handled directly in Admin::adminMenu() via
        // smartpay_get_activated_integrations() to preserve menu position.
    }
}
