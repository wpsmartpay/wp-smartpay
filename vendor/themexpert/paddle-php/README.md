# Paddle API PHP Client

PHP SDK for Paddle Payment Gateway. Support api `v1` and `v2` out-of-the-box.

Paddle features supported:

-   Create product while checkout and validate response
-   Subscription creation
-   Coupone management
-   License management
-   Transaction and pay slip generate
-   Checkout API
-   Webhook support

## Install

Via Composer

```bash
$ composer require themexpert/paddle-php
```

## Usage

```php
use ThemeXpert\Paddle\Paddle;

Paddle::setApiCredentials('paddle_vendor_id', 'paddle_auth_code');

echo Product::list();
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
