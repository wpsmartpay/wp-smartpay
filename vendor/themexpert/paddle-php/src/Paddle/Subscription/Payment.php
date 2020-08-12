<?php

namespace ThemeXpert\Paddle\Subscription;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;
use ThemeXpert\Paddle\Paddle;

class Payment extends ApiResource
{
    const CLASS_URL = 'subscription';

    public static $credentials = array();

    public static function init()
    {
        self::$credentials = Paddle::getApiCredentials();
    }

    public static function list(int $subscriptionId, string $planId, array $paymentData): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'payments');

        $bodyData =  array_merge(self::$credentials, ['subscription_id' => $subscriptionId, 'plan_id' => $planId], $paymentData);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }

    public static function reschedule(int $paymentId, string $date): string
    {
        self::init();

        $url = self::vendorUrl(self::CLASS_URL . '/' . 'payments_reschedule');

        $bodyData =  array_merge(self::$credentials, ['payment_id' => $paymentId, 'date' => date("Y-m-d", strtotime($date))]);

        return CurlClient::sendHttpRequest($url, 'POST', $bodyData);
    }
}
