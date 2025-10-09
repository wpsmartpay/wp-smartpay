# Smartpay Forms
## Admin Panel Form
### smartpay_form_created / smartpay_form_updated / smartpay_form_deleted

- Type: Action
- Fires: On Form created, updated and deleted

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

Parameters:

- `$form` (array|object) — Form configuration.

```php
add_action('before_smartpay_payment_form', function( $form ) {
    echo '<div class="notice">Special promo available today!</div>';
});
```


## Checkout Form (Frontend - Modal)

### smartpay_product_modal_popup_content / smartpay_before_product_payment_form_button / smartpay_after_product_payment_form_button

- Type: Action
- Fires: Within the modal checkout UI to inject content before/after the pay button.

Parameters:

- `$product` (SmartPay\Models\Product|null)

```php
add_action('smartpay_product_modal_popup_content', function( $product ) {
    echo '<p class="text-muted">Secure checkout</p>';
});
```
