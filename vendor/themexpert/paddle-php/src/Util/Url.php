<?php

namespace ThemeXpert\Paddle\Util;

class Url
{
    public static function getUrl($base, $version = '2.0', $path)
    {
        return "{$base}/api/{$version}/${path}";
    }

    public static function checkoutUrl($path, $version = '2.0')
    {
        return static::getUrl(PADDLE_CHECKOUT_URL, $version, $path);
    }

    public static function vendorUrl($path, $version = '2.0')
    {
        return static::getUrl(PADDLE_VENDOR_URL, $version, $path);
    }
}
