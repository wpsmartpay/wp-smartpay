<?php if ('embedded' == $behavior) : ?>

<?php include  __DIR__ . '/shared/product_details.php'; ?>

<?php else : ?>
<div class="smartpay" style="margin: 0 auto; background: transparent;">
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