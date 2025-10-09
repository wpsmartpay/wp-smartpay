# Smartpay Availability
## smartpay_loaded / smartpay_init

- Type: Action
- Fires: On `plugins_loaded` and `init` respectively as SmartPay boots.

Parameters: none

```php
add_action('smartpay_loaded', function() {
    // Early bootstrap hooks.
});
```
You can also check Smartpay availability by a CONSTANT `SMARTPAY_VERSION`

```php
if (!defined('SMARTPAY_VERSION')) {
	// Admin notice that Smartpay is not installed
}
```
