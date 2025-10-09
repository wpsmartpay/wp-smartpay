# Smartpay Availability
## smartpay_customer_user_created

- Type: Action
- Fires: After a WP user is programmatically created for a payer (if enabled).

Parameters:

- `$user` (int|WP_User) — New user ID or object.
- `$payment` (SmartPay\Models\Payment)

```php
add_action('smartpay_customer_user_created', function( $user, $payment ) {
    // Assign role, send welcome sequence, etc.
}, 10, 2);
```

## smartpay_customer_updated

- Type: Action
- Fires: After a customer profile is updated via REST.

Parameters:

- `$customer` (SmartPay\Models\Customer)
- `$requestData` (array) — Raw request body.

```php
add_action('smartpay_customer_updated', function( $customer, $requestData ) {
    // Sync to external CRM.
}, 10, 2);
```
