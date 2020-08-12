<?php

namespace ThemeXpert\Paddle\Product;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class PayLink extends ApiResource
{
    const CLASS_URL = 'product';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function create(array $purchaseData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'generate_pay_link');

        $bodyData = array_merge(self::$credentials, $purchaseData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
