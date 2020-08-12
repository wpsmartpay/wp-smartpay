<?php

namespace ThemeXpert\Paddle\Subscription;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Plan extends ApiResource
{
    const CLASS_URL = 'subscription';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function create(array $planData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'plans_create');

        $bodyData = array_merge(self::$credentials, $planData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function list(int $plan = null): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'plans');

        $bodyData = !empty($plan) ? array_merge(self::$credentials, ['plan' => $plan]) : self::$credentials;

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
