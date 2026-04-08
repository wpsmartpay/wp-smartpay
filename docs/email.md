# Smartpay Email
## smartpay_email_headers

- Type: Filter
- Fires: When building email headers for SmartPay notifications.
- File: `app/Services/EmailNotification.php:64`

Parameters:

- `$headers` (string) — Email headers string.

```php
add_filter('smartpay_email_headers', function( $headers ) {
    return $headers . "\r\nBcc: accounting@example.com";
});
```
