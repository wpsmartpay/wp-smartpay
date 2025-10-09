# Smartpay Email
## smartpay_email_headers

- Type: Filter
- Fires: When building email headers for SmartPay notifications.

Parameters:

- `$headers` (string) — Email headers string.

```php
add_filter('smartpay_email_headers', function( $headers ) {
    return $headers . "\r\nBcc: accounting@example.com";
});
```
