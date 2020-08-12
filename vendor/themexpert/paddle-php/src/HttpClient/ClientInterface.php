<?php

namespace ThemeXpert\Paddle\HttpClient;

interface ClientInterface
{
    public static function sendHttpRequest(string $url, string $method, array $bodyData = null, array $config = array()): string;
}
