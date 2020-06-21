<?php

namespace ThemeXpert\Paddle\Product;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Coupon extends ApiResource
{
    const CLASS_URL = 'product';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function create(array $couponData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'create_coupon');

        $bodyData = array_merge(self::$credentials, $couponData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function list(string $productId): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'list_coupons');

        $bodyData = array_merge(self::$credentials, ['product_id' => $productId]);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function update(string $couponCode, string $group, array $couponData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'update_coupon');

        $bodyData = array_merge(self::$credentials, ['coupon_code' => $couponCode], ['group' => $group], $couponData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function delete(array $couponData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL) . '/' . 'delete_coupon';

        $bodyData = array_merge(self::$credentials, $couponData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
