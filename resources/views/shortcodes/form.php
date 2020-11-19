<?php
$form_action = smartpay_get_payment_page_uri();
$gateways = smartpay_get_enabled_payment_gateways(true);

$_gateway = \sanitize_text_field($_REQUEST['gateway'] ?? '');

$chosen_gateway = isset($_gateway) && smartpay_is_gateway_active($_gateway) ? $_gateway : smartpay_get_default_gateway();
$has_payment_error = false;
?>

<?php if ('embedded' == $behavior) : ?>
    <?php include __DIR__ . '/shared/form_details.php';
    ?>
<?php else : ?>
    <div class="smartpay" style="margin: 0 auto; background: transparent;">
        <div class="smartpay-product-shortcode">
            <div class="modal fade product-modal">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl py-5">
                    <div class="modal-content align-content-between" style="background: transparent; border: 0;">
                        <div class="d-flex justify-content-end">
                            <div class="modal-button py-3">
                                <button type="button" class="btn modal-close close" data-dismiss="modal"><i data-feather="x" width="21" height="21"></i></button>
                            </div>
                        </div>
                        <div class="modal-body text-center p-0">
                            <?php include __DIR__ . '/shared/form_details.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-success open-product-modal m-1">
                <?php echo _e($label ?: 'Pay now', 'smartpay'); ?>
            </button>
        </div>
    </div>
<?php endif; ?>