<?php

namespace ThemeXpert\Paddle\Checkout;

use ThemeXpert\Paddle\ApiResource;
use ThemeXpert\Paddle\HttpClient\CurlClient;

class Price extends ApiResource
{
    const CLASS_URL = 'prices';

    public static function get(array $productIds, array $coupons, string $customerCountry = null, string $customerIp = null): string
    {
        if (empty($customerCountry) && empty($customerIp)) {
            // TODO:: Add Exception
            die('You must pass customer country or IP');
        }

        $url = self::checkoutUrl(self::CLASS_URL) . '?product_ids=' . implode(',', $productIds) . '&coupons=' . implode(',', $coupons);

        $url .= $customerCountry ? '&customer_country=' . $customerCountry :  '&customer_ip=' . $customerIp;

        return CurlClient::sendHttpRequest($url, 'GET');
    }
}
