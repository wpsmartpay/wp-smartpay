<div class="smartpay" id="smartpay-metabox" style="margin: -6px -12px -12px -12px;">
    <div class="d-flex">
        <div class="col-12 border-bottom pb-1 pt-3">
            <div class="form-group">
                <!-- <label for="discount_type" class="text-muted my-2 d-block"><strong><?php _e('Short description', 'smartpay'); ?></strong></label> -->
                <textarea class="form-control" id="description" name="description" placeholder="<?php _e('Coupon description', 'smartpay'); ?>"><?php echo $coupon->description ?? ''; ?></textarea>
            </div>
        </div>
    </div>
    <div class="d-flex">
        <div class="col-3 bg-light border-right">
            <div class="py-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link text-decoration-none d-flex align-items-center active" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">
                        <i data-feather="file" width="14" height="14"></i> <span class="ml-2"><?php _e('General', 'smartpay'); ?></span>
                    </a>
                    <a class="nav-link text-decoration-none d-flex align-items-center" id="usage-restriction-tab" data-toggle="pill" href="#usage-restriction" role="tab" aria-controls="usage-restriction" aria-selected="false">
                        <i data-feather="lock" width="14" height="14"></i> <span class="ml-2"><?php _e('Usage restriction', 'smartpay'); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-9 mb-3">
            <div class="tab-content py-3" id="smartpay-tabContent">
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="form-row">
                        <div class="col-7">
                            <div class="form-group">
                                <label for="discount_type" class="text-muted my-2 d-block"><strong><?php _e('Discount type', 'smartpay'); ?></strong></label>
                                <select class="form-control" id="discount_type" name="discount_type" selected="<?php echo $coupon->discount_type ?? ''; ?>">
                                    <option value="fixed" <?php echo ('afixedbc' === $coupon->discount_type) ? 'selected' : ''; ?>><?php _e('Fixed amount', 'smartpay'); ?></option>
                                    <option value="percentage" <?php echo ('percentage' === $coupon->discount_type) ? 'selected' : ''; ?>><?php _e('Percentage', 'smartpay'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="discount_amount" class="text-muted my-2 d-block"><strong><?php _e('Coupon amount', 'smartpay'); ?></strong></label>
                                <input type="text" class="form-control" id="discount_amount" name="discount_amount" placeholder="0" value="<?php echo $coupon->discount_amount ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="expiry_date" class="text-muted my-2 d-block"><strong><?php _e('Coupon expiry date', 'smartpay'); ?></strong></label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?php echo $coupon->expiry_date ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="usage-restriction" role="tabpanel" aria-labelledby="usage-restriction-tab">
                    <?php _e('Upgrade to pro', 'smartpay'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
