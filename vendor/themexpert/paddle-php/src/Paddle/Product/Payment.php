<?php

namespace ThemeXpert\Paddle\Product;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Payment extends ApiResource
{
    private static $classUrl = 'payment';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function refund(string $orderId, string $amount = '', string $reason = ''): string
    {
        self::init();

        $url = self::vendorUrl(self::$classUrl . '/' . 'refund');
        
        $postdata = ['order_id' => $orderId];
        
        if($amount) $postdata['amount'] = (float) $amount;
        
        if($reason) $postdata['reason'] = $reason;

        $bodyData = array_merge(self::$credentials, $postdata);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
