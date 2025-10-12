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

## smartpay_customer_dashboard_tab_link / smartpay_customer_dashboard_tab_content

- Type: Action
- Fires: To add custom tabs and tab content to the customer dashboard.
- File: `resources/views/shortcodes/customer_dashboard.php:31,198`

Parameters:

- `$customer` (SmartPay\Models\Customer) — For content hook.
- `$payments` (Illuminate\Support\Collection) — For content hook.

```php
add_action('smartpay_customer_dashboard_tab_link', function() {
    echo '<a class="nav-link mx-2 px-4" data-toggle="pill" href="#mytab" role="tab">My Tab</a>';
});

add_action('smartpay_customer_dashboard_tab_content', function( $customer, $payments ) {
    echo '<div class="tab-pane fade" id="mytab" role="tabpanel">Hello!</div>';
}, 10, 2);
```
