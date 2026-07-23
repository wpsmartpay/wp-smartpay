<?php

namespace SmartPay\Modules\Integration;

defined('ABSPATH') || exit;

use SmartPay\Foundation\Integration;

class Products extends Integration
{
    public static function config(): array
    {
        return [
            'name'       => __( 'Products', 'smartpay' ),
            'excerpt'    => __( 'Sell digital products and downloads directly from your WordPress site.', 'smartpay' ),
            'cover'      => SMARTPAY_PLUGIN_ASSETS . '/img/integrations/products.png',
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
