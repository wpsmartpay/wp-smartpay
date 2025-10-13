# Smartpay Products
## smartpay_product_created / smartpay_product_updated / smartpay_product_deleted

- Type: Action
- Fires: On product created, updated and deleted
- File: `app/Models/Product.php:36,41,46`

Parameters:

- `$product` (SmartPay\Models\Product) - Product Model

```php
add_action('smartpay_product_created', function( $product ) {
    // Index product in search service.
});
```

## Checkout Form (Frontend - Modal)

### smartpay_product_modal_popup_content / smartpay_before_product_payment_form_button / smartpay_after_product_payment_form_button

- Type: Action
- Fires: Within the modal checkout UI to inject content before/after the pay button.
- File: `resources/views/shortcodes/shared/payment_modal.php:44,101,107`

Parameters:

- `$product` (SmartPay\Models\Product|null)

```php
add_action('smartpay_product_modal_popup_content', function( $product ) {
    echo '<p class="text-muted">Secure checkout</p>';
});
```


<!-- Filter Hooks -->

## smartpay_product_is_purchasable

- Type: Filter
- Fires: When checking if a product can be purchased.
- File: `app/Models/Product.php:147`

Parameters:

- `$isPurchasable` (bool) — Default purchasable state.
- `$product` (SmartPay\Models\Product) — Product Model.

```php
add_filter('smartpay_product_is_purchasable', function( $is_purchasable, $product ) {
    return $product->status === 'publish' ? $is_purchasable : false;
}, 10, 2);
```
