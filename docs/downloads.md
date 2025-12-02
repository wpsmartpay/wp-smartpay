# Smartpay Download
## smartpay_download_access_denied

- Type: Action
- Fires: When a download link validation fails (invalid token/permission).
- File: `app/Modules/Frontend/Utilities/Downloader.php:58`

Parameters:

- `$args` (array) — Query args for the request.
- `$validation` (array) — Validation result details.

```php
add_action('smartpay_download_access_denied', function( $args, $validation ) {
    // Log abuse attempts.
}, 10, 2);
```

## smartpay_download_payment_invalid

- Type: Action
- Fires: When the associated payment is invalid or not completed.
- File: `app/Modules/Frontend/Utilities/Downloader.php:68`

Parameters:

- `$args` (array)
- `$validation` (array)
- `$payment` (SmartPay\Models\Payment|null)

```php
add_action('smartpay_download_payment_invalid', function( $args, $validation, $payment ) {
    // Notify customer or support.
}, 10, 3);
```

## smartpay_download_product_invalid

- Type: Action
- Fires: When the product requested for download is invalid or not permitted.
- File: `app/Modules/Frontend/Utilities/Downloader.php:75`

Parameters:

- `$args` (array)
- `$validation` (array)
- `$payment` (SmartPay\Models\Payment)
- `$product` (SmartPay\Models\Product|null)

```php
add_action('smartpay_download_product_invalid', function( $args, $validation, $payment, $product ) {
    // Handle invalid product download attempt.
}, 10, 4);
```

## smartpay_before_download_delivery

- Type: Action
- Fires: Right before sending the download file to the browser.
- File: `app/Modules/Frontend/Utilities/Downloader.php:151`

Parameters:

- `$args` (array)
- `$validation` (array)
- `$payment` (SmartPay\Models\Payment)
- `$product` (SmartPay\Models\Product)
- `$requestedFile` (array) — File metadata from product files list.

```php
add_action('smartpay_before_download_delivery', function( $args, $validation, $payment, $product, $file ) {
    // Add logging, watermarking, or counter updates.
}, 10, 5);
```
