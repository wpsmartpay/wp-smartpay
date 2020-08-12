<?php

namespace ThemeXpert\Paddle\Subscription;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class User extends ApiResource
{
    const CLASS_URL = 'subscription';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function list(array $bodyData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'users');

        $bodyData =  array_merge(self::$credentials, $bodyData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function cancel(int $subscriptionId): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'users_cancel');

        $bodyData =  array_merge(self::$credentials, ['subscription_id' => $subscriptionId]);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function update(int $subscriptionId, array $subscriptionData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'users/update');

        $bodyData =  array_merge(self::$credentials, ['subscription_id' => $subscriptionId], $subscriptionData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function previewUpdate(int $subscriptionId, array $bodyData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'preview_update');

        $bodyData =  array_merge(self::$credentials, ['subscription_id' => $subscriptionId], $bodyData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
