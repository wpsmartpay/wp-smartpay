# Smartpay Forms
## Admin Panel Form
### smartpay_form_created / smartpay_form_updated / smartpay_form_deleted

- Type: Action
- Fires: On Form created, updated and deleted
- File: `app/Models/Form.php:33,38,43`

Parameters:

- `$form` (SmartPay\Models\Form) - Form Model

```php
add_action('smartpay_form_updated', function( $form ) {
    // Sync form fields to CRM.
});
```

## Checkout Form (Frontend)

### before_smartpay_payment_form / before_smartpay_payment_form_button / after_smartpay_payment_form_button / after_smartpay_payment_form

- Type: Action
- Fires: On Before Payment form, before form button, after form button and after form
- `resources/views/shortcodes/shared/form_details.php:5,146,153,155`

Parameters:

- `$form` (array|object) — Form configuration.

```php
add_action('before_smartpay_payment_form', function( $form ) {
    echo '<div class="notice">Special promo available today!</div>';
});
```
