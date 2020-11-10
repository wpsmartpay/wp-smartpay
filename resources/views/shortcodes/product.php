<?php
$product_price = $product->sale_price > -1 ? $product->sale_price : $product->base_price;

// $form_action = smartpay_get_payment_page_uri();
$form_action = '#';
// $gateways = smartpay_get_enabled_payment_gateways(true);
$gateways = [];

$_gateway = \sanitize_text_field($_REQUEST['gateway'] ?? '');

// $chosen_gateway = isset($_gateway) && smartpay_is_gateway_active($_gateway) ? $_gateway : smartpay_get_default_gateway();
$chosen_gateway = 'paddle';
$has_payment_error = false;
?>

<?php if ('embedded' == $behavior) : ?>
<?php //include  __DIR__ . '/shared/product_details.php'; 
    ?>

<?php else : ?>
<div class="smartpay" style="background: transparent;">
    <div class="smartpay-product-shortcode">
        <div class="modal fade product-modal">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
                <div class="modal-content align-content-between" style="background: transparent; border: 0;">
                    <div class="modal-body text-center p-0">
                        <?php include  __DIR__ . '/shared/product_details.php';
                            ?>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-success open-product-modal m-1">
            <?php echo _e($label ?: 'Buy now', 'smartpay'); ?>
        </button>
    </div>
</div>
<?php endif; ?>