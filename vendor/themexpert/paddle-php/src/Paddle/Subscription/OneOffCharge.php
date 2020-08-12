<?php

namespace ThemeXpert\Paddle\Subscription;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class OneOffCharge extends ApiResource
{
    const CLASS_URL = 'subscription';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function create(int $id, string $amount, string $chargeName): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . $id . '/' . 'charge');

        // Check max length of charge_name
        // TODO: Add Exception
        strlen($chargeName) > 50 ? die("Charge name shouldn't more than 50") : '';

        $bodyData =  array_merge(self::$credentials, ['amount' => $amount, 'charge_name' => $chargeName]);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
