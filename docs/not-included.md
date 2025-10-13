# Not Included in the documentation
## Gateways (PayPal)

smartpay_paypal_subscription_process_payment

- Type: Action
- Fires: When preparing PayPal subscription payment arguments.
- File: `app/Modules/Gateway/Gateways/PaypalStandard.php:137`

Parameters:

- `$payment` (SmartPay\Models\Payment)
- `$paymentData` (array)

```php
add_action('smartpay_paypal_subscription_process_payment', function( $payment, $paymentData ) {
    // Add custom trial or meta.
}, 10, 2);
```

smartpay_paypal_redirect_args

- Type: Filter
- Fires: Before redirecting to the PayPal URL; modify query parameters.
- File: `app/Modules/Gateway/Gateways/PaypalStandard.php:151`

Parameters:

- `$args` (array) — PayPal arguments.
- `$paymentData` (array)

```php
add_filter('smartpay_paypal_redirect_args', function( $args, $paymentData ) {
    $args['custom_param'] = 'value';
    return $args;
}, 10, 2);
```

smartpay_paypal_web_accept / smartpay_paypal_{txn_type}

- Type: Action
- Fires: On IPN notification to handle specific transaction types; falls back to `web_accept`.
- File: `app/Modules/Gateway/Gateways/PaypalStandard.php:72,279,283`

Parameters:

- `$ipn` (array) — Raw IPN data.
- `$payment_id` (int)

```php
add_action('smartpay_paypal_web_accept', function( $ipn, $payment_id ) {
    // Verify and reconcile payment.
}, 10, 2);
```

smartpay_paypal_uri

- Type: Filter
- Fires: When resolving the PayPal endpoint URL (sandbox/live).
- File: `app/Modules/Gateway/Gateways/PaypalStandard.php:474`

Parameters:

- `$paypal_uri` (string)
- `$ssl_check` (bool)
- `$ipn` (bool)

```php
add_filter('smartpay_paypal_uri', function( $uri, $ssl, $ipn ) {
    return $uri; // Override if needed.
}, 10, 3);
```
