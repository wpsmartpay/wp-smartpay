<?php

namespace ThemeXpert\Paddle\Product;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Transaction extends ApiResource
{
    const CLASS_URL = 'product';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function list(string $entity, int $id, int $page = null): string
    {
        // TODO:: Verify $entity as only  User ID, Subscription ID, Order ID, Checkout ID (hash) or Product ID
        self::init();

        $url = self::vendorUrl("{$entity}/{$id}/transactions");
        // die($url);

        $bodyData = !empty($page) ? array_merge(self::$credentials, ['page' => $page]) : self::$credentials;

        // die(print_r($bodyData));

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
