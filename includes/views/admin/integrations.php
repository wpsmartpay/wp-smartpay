<div class="smartpay">
    <div class="wrap container">
        <h1 class="wp-heading-inline"></h1>
    </div>
    <div class="container integrations">
        <div class="d-flex align-items-center justify-content-between py-1 px-4 mt-3 text-white bg-dark rounded-top">
            <div class="lh-100">
                <h2 class="text-white"><?php _e('SmartPay - Integrations', 'smartpay'); ?></h2>
            </div>
            <div>
                <a class="btn btn-dark btn-sm" href="https://wpsmartpay.com/changelog/" target="_blank">v<?php echo SMARTPAY_VERSION; ?></a>
            </div>
        </div>

        <div class="card border-light shadow-sm mt-0">
            <div class="card-body">
                <div class="row">
                    <?php for ($i = 0; $i < 10; $i++) : ?>
                        <div class="col-lg-3 col-md-4 integration">
                            <div class="card p-3 d-flex">
                                <div class="image m-0 mb-3 text-center">
                                    <img src="<?php echo SMARTPAY_PRO_PLUGIN_ASSETS . '/modules/stripe/images/cover.png' ?>" class="img-fluid" alt="">
                                </div>
                                <div class="info">
                                    <h3 class="name m-0 mb-1">Paddle</h3>
                                    <p class="excerpt mb-1">Paddle is payment gateway</p>
                                </div>

                                <div class="actions d-flex align-items-center mt-2 border-top pt-2">
                                    <!-- <a href="#" class="btn btn-sm flex-grow-1 text-decoration-none btn-primary">Upgrade to pro</a> -->
                                    <div class="custom-control custom-switch custom-switch-lg"><input type="hidden" name="smartpay_settings[test_mode]" value="0"><input type="checkbox" class="custom-control-input" id="smartpay_settings[test_mode]" name="smartpay_settings[test_mode]" value="1"><label class="custom-control-label" for="smartpay_settings[test_mode]"></label></div>

                                    <span class="ml-2">
                                        Currently Disabled
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<style>

</style>
<?php echo ob_get_clean(); ?>