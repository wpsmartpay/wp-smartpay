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
