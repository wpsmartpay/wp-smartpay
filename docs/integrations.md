# Smartpay Availability
## smartpay_integration_{namespace}_loaded

- Type: Action (dynamic)
- Fires: After an active integration namespace is booted.

Parameters: none

```php
add_action('smartpay_integration_mailchimp_loaded', function() {
    // Initialize mapping, etc.
});
```

## smartpay_integrations_loaded

- Type: Action
- Fires: After all integrations have been processed.

Parameters: none

```php
add_action('smartpay_integrations_loaded', function() {
    // Post-load tasks.
});
```

<!-- Filter Hooks -->

## smartpay_integrations / smartpay_integration_manager / smartpay_integration_get_not_installed_message

- Type: Filters
Purpose: Customize available integrations, override manager resolution, and alter the not-installed message.

```php
add_filter('smartpay_integrations', function( $integrations ) {
    $integrations['mygateway'] = [ 'name' => 'My Gateway', 'manager' => My\Gateway::class, 'type' => 'pro', 'categories' => ['Payment Gateway'] ];
    return $integrations;
});
```
