<?php

namespace ThemeXpert\Paddle\Subscription;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Modifier extends ApiResource
{
    const CLASS_URL = 'subscription';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function list(int $subscriptionId, string $planId): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'modifiers');

        $bodyData =  array_merge(self::$credentials, ['subscription_id' => $subscriptionId, 'plan_id' => $planId]);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function create(int $subscriptionId, array $modifierData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'modifiers' . '/' . 'create');

        $bodyData =  array_merge(self::$credentials, ['subscription_id' => $subscriptionId], $modifierData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function delete(int $modifierId): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'modifiers' . '/' . 'delete');

        $bodyData =  array_merge(self::$credentials, ['modifier_id' => $modifierId]);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
