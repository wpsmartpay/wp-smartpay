# Smartpay Payment
## smartpay_before_payment_processing

- Type: Action
- Fires: Right before processing a payment request, after request validation and data preparation.

Parameters:

- `$payment_data` (array) — Prepared payment payload to be processed.

```php
add_action('smartpay_before_payment_processing', function( $payment_data ) {
    // Inspect or modify pending payment data, log, etc.
});
```

## smartpay_{gateway}_ajax_process_payment

- Type: Action (dynamic)
- Fires: When initiating an AJAX-based gateway charge for a selected gateway.

Parameters:

- `$paymentData` (array) — Payment data to send to the gateway.

```php
add_action('smartpay_stripe_ajax_process_payment', function( $paymentData ) {
    // Handle Stripe AJAX payment initiation.
});
```

## smartpay_{gateway}_process_payment

- Type: Action (dynamic)
- Fires: When initiating a non-AJAX gateway charge for a selected gateway.

Parameters:

- `$paymentData` (array) — Payment data to send to the gateway.

```php
add_action('smartpay_free_process_payment', function( $paymentData ) {
    // Mark free orders as paid immediately.
});
```

## smartpay_payment_created

- Type: Action
- Fires: After a payment record is created and saved.

Parameters:

- `$payment` (SmartPay\Models\Payment) — Newly created payment model.

```php
add_action('smartpay_payment_created', function( $payment ) {
    // Notify services, Queue receipt email, analytics tracking, etc.
});
```

## smartpay_payment_completed

- Type: Action
- Fires: When a payment status becomes completed.

Parameters:

- `$payment` (SmartPay\Models\Payment) — Completed payment model.

```php
add_action('smartpay_payment_completed', function( $payment ) {
    // Fulfill order, grant access, license activation, etc.
});
```

## smartpay_payment_cancelled

- Type: Action
- Fires: When a payment transitions to cancelled states.

Parameters:

- `$payment` (SmartPay\Models\Payment)

```php
add_action('smartpay_payment_cancelled', function( $payment ) {
    // Revoke access, notify user, restore inventory.
});
```

## martpay_payment_failed / smartpay_payment_refunded / smartpay_payment_abandoned

- Type: Action
- Fires: On specific failure/refund/abandoned status changes.

Parameters:

- `$payment` (SmartPay\Models\Payment) - Payment Model

```php
add_action('smartpay_payment_failed', function( $payment ) {
    // Alert support team.
});
```

## smartpay_before_payment_receipt / smartpay_before_payment_receipt_data / smartpay_after_payment_receipt / smartpay_payment_{gateway}_receipt

- Type: Action
- Fires: Around the payment receipt rendering and for gateway-specific (dynamic) receipt sections.

Parameters:

- `$payment` (SmartPay\Models\Payment) - Payment Model

```php
add_action('smartpay_payment_paypal_receipt', function( $payment ) {
    echo '<p>PayPal Transaction: ' . esc_html($payment->transaction_id) . '</p>';
});
```



<!-- Filter Hooks -->
## smartpay_prepare_payment_data

- Type: Filter
- Fires: When building the normalized payment payload from request data.
- File: `app/Modules/Payment/Payment.php:150`

Parameters:

- `$prepared` (array) — Payment data array.
- `$_data` (array) — Raw request data.

```php
add_filter('smartpay_prepare_payment_data', function( $prepared, $_data ) {
    $prepared['extra']['source'] = 'landing-page-7';
    return $prepared;
}, 10, 2);
```


## smartpay_payment_extra_data

- Type: Filter
- Fires: Before saving the `extra` payload on the payment model.

Parameters:

- `$extra` (array) — Extra payment data.

```php
add_filter('smartpay_payment_extra_data', function( $extra ) {
    $extra['utm'] = $_COOKIE['utm'] ?? [];
    return $extra;
});
```
