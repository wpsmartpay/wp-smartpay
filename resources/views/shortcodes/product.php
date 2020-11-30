<?php if ('embedded' == $behavior) : ?>

<?php include __DIR__ . '/shared/product_details.php'; ?>

<?php else : ?>
<div class="smartpay" style="margin: 0 auto; background: transparent;">
    <div class="smartpay-product-shortcode">
        <div class="modal fade product-modal">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
                <div class="modal-content align-content-between" style="background: transparent; border: 0;">
                    <div class="d-flex justify-content-end">
                        <div class="modal-button py-3">
                            <button type="button" class="btn modal-close close" data-dismiss="modal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body text-center p-0 mx-5">
                        <?php include __DIR__ . '/shared/product_details.php';
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