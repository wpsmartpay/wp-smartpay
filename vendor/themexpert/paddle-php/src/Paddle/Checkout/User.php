<?php

namespace ThemeXpert\Paddle\Checkout;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;
use ThemeXpert\Paddle\Util\Util;

class User extends ApiResource
{
    const CLASS_URL = 'user';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function history(string $email, string $product_id): string
    {
        self::init();

        $email = Util::filterValidEmail($email);

        $url = self::checkoutUrl(self::CLASS_URL) . '/' . 'history' . '?vendor_id=' . self::$credentials['vendor_id'] . '&product_id=' . $product_id . '&email=' . $email;

        return CurlClient::sendHttpRequest($url, 'GET');
    }
}
