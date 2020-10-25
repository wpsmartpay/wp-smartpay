<div class="smartpay" id="smartpay-metabox" style="margin: -6px -12px -12px -12px;">
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
                                <label for="type" class="text-muted my-2 d-block"><strong><?php _e('Discount type', 'smartpay'); ?></strong></label>
                                <select class="form-control" id="type" name="type">
                                    <option value="fixed"><?php _e('Fixed amount', 'smartpay'); ?></option>
                                    <option value="percentage"><?php _e('Percentage', 'smartpay'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="amount" class="text-muted my-2 d-block"><strong><?php _e('Coupon amount', 'smartpay'); ?></strong></label>
                                <input type="text" class="form-control" id="amount" name="amount" placeholder="0">
                            </div>

                            <div class="form-group">
                                <label for="expire_date" class="text-muted my-2 d-block"><strong><?php _e('Coupon expiry date', 'smartpay'); ?></strong></label>
                                <input type="date" class="form-control" id="expire_date" name="expire_date" placeholder="Option name">
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
