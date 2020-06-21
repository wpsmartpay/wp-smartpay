<?php

namespace ThemeXpert\Paddle\HttpClient;

use ThemeXpert\Paddle\Util\Util;

class CurlClient implements ClientInterface
{
    public static function sendHttpRequest(string $url, string $method, array $bodyData = null, array $config = array()): string
    {
        // Check if cURL is not enabled
        // TODO: Add Exception
        !extension_loaded('curl') ? die('You must enable cURL to the server.') : '';

        $url = Util::utf8($url);

        // Initialize cURL
        $curl = curl_init();

        // Set data to cURL
        // TODO: Set config data
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => json_encode($bodyData),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));

        // Get cURL responce
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $err ? 'cURL Error: ' . $err : $response;
    }
}
