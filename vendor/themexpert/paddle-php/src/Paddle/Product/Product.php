<?php

namespace ThemeXpert\Paddle\Product;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Product extends ApiResource
{
    const CLASS_URL = 'product';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function list(): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'get_products');

        $bodyData = self::$credentials;

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
