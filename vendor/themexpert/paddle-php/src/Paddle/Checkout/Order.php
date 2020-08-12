<?php

namespace ThemeXpert\Paddle\Checkout;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Util\Util;

class Order extends ApiResource
{
    const CLASS_URL = 'order';

    public static function details(string $checkoutId, string $callbackUrl): string
    {
        $callbackUrl = Util::filterValidUrl($callbackUrl);

        $url = self::checkoutUrl(self::CLASS_URL, '1.0') . '?checkout_id=' . $checkoutId . '&callback=' . $callbackUrl;

        return CurlClient::sendHttpRequest($url, 'GET');
    }
}
