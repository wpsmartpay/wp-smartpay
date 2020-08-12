<?php

namespace ThemeXpert\Paddle;

define("PADDLE_VENDOR_URL", "https://vendors.paddle.com");
define("PADDLE_CHECKOUT_URL", "https://checkout.paddle.com");

final class Paddle
{
    /**
     * Your Paddle Vendor/Account ID
     * @var string
     */
    private static $vendorId;

    /**
     * Your Paddle Code/Token
     * @var string
     */
    private static $authCode;

    /**
     * Your Paddle Public Key
     * @var string
     */
    private static $publicKey;

    /**
     * Cloning is forbidden.
     *
     * This method protect the class to cloneing instance.
     *
     * @access public
     */
    public function __clone()
    {
        die('Cloning is forbidden!');
    }

    /**
     * Unserialize instances of this class is forbidden.
     *
     * This method protect the class to create unserialize instances.
     *
     * @access public
     */
    public function __wakeup()
    {
        die('Unserialize instances is forbidden!');
    }

    /**
     * Construct Main class SmartPay.
     *
     * @access public
     */
    public function __construct(string $vendorId = null, string $authCode = null, string $publicKey = null)
    {

        if ($vendorId && $authCode) {
            self::setApiCredentials($vendorId, $authCode, $publicKey);
        }
    }

    public static function setApiCredentials(string $vendorId, string $authCode, string $publicKey = null): bool
    {
        self::$vendorId   = (int) trim($vendorId)   ?? null;
        self::$authCode   = trim($authCode)         ?? null;
        self::$publicKey  = trim($publicKey)        ?? null;

        return true;
    }

    public static function setApiPublicKey(string $publicKey): bool
    {
        self::$publicKey = trim($publicKey) ?? null;

        return true;
    }

    public static function getApiCredentials(): array
    {
        if (empty(self::$vendorId) || empty(self::$authCode)) {
            // TODO: Add Exception
            die('You must enter your Vendor ID and Auth Codes.');
        }

        return array(
            'vendor_id'  => self::$vendorId,
            'vendor_auth_code'  => self::$authCode,
            'paddle_public_key' => self::$publicKey,
        );
    }

    public static function getApiPublicKey(): string
    {
        return self::$publicKey;
    }

    public static function unSetApiCredentials(): bool
    {
        unset(self::$vendorId);
        unset(self::$authCode);
        unset(self::$publicKey);

        return true;
    }

    public function __distrust()
    {
        self::unSetApiCredentials();
    }
}
