<?php

namespace ThemeXpert\Paddle;

class Verify extends ApiResource
{
    /**
     * Your Paddle Public Key
     * @var string
     */
    private static $publicKey;

    public function __construct(string $publicKey = null)
    {
        if ($publicKey) {
            self::setApiPublicKey($publicKey);
        }
    }

    public static function setApiPublicKey(string $publicKey): bool
    {
        self::$publicKey = trim($publicKey) ?? null;

        return true;
    }

    public static function getApiPublicKey(): string
    {
        if (empty(self::$publicKey)) {
            self::$publicKey = Paddle::getApiPublicKey();
            if (empty(self::$publicKey)) {
                // TODO: Add Exception
                die('You must enter your Public Key.');
            }
        }

        return self::$publicKey;
    }

    public static function webHookSignature(string $signature, array $webHookData, string $publicKey = null): array
    {
        if (!empty($publicKey)) {
            self::setApiPublicKey($publicKey);
        }

        if ($publicKey = self::getApiPublicKey()) {

            $public_key = openssl_get_publickey($publicKey);

            // Get the p_signature parameter & base64 decode it.
            $signature = base64_decode($signature);

            if (isset($webHookData['p_signature'])) {
                unset($webHookData['p_signature']);
            };

            // ksort() and serialize the fields
            ksort($webHookData);

            foreach ($webHookData as $k => $v) {
                if (!in_array(gettype($v), array('object', 'array'))) {
                    $webHookData[$k] = "$v";
                }
            }

            $data = serialize($webHookData);

            // Verify the signature
            if (openssl_verify($data, $signature, $public_key, OPENSSL_ALGO_SHA1)) {
                $response =  array(
                    'success'   => true,
                    'response'  => array(
                        'message' => 'Yay! Signature is valid!'
                    ),
                );
            } else {
                $response =  array(
                    'success'   => false,
                    'error'  => array(
                        'message' => 'The signature is invalid!'
                    ),
                );
            }
        } else {
            $response =  array(
                'success'   => false,
                'error'  => array(
                    'message' => 'Your Public Key is not set.'
                ),
            );
        }

        return $response;
    }
}