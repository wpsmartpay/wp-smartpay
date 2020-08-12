<?php

namespace ThemeXpert\Paddle\Product;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class License extends ApiResource
{
    const CLASS_URL = 'product';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function generate(string $productId, int $allowedUses, string $expiresAt = null): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'generate_license');

        $bodyData = array_merge(self::$credentials, ['product_id' => $productId, 'allowed_uses' => $allowedUses]);

        if (!empty($expiresAt)) {
            $bodyData = array_merge($bodyData, ['expires_at' => date("Y-m-d", strtotime($expiresAt))]);
        }

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
