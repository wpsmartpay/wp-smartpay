<?php

$integrations = smartpay_integrations();

// dd($integrations);

?>


<div class="smartpay">
    <div class="wrap container">
        <h1 class="wp-heading-inline"></h1>
    </div>
    <div class="container smartpay-integrations">
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
                    <?php foreach (smartpay_integrations() as $namespace => $integration) : ?>
                        <div class="col-lg-3 col-md-4 integration">
                            <div class="card p-3 d-flex">
                                <div class="image m-0 mb-3 text-center">
                                    <img src="<?php echo $integration['cover']; ?>" class="img-fluid" alt="">
                                </div>
                                <div class="info">
                                    <h3 class="name m-0 mb-1"><?php echo $integration['name']; ?></h3>
                                    <p class="excerpt mb-1"><?php echo $integration['excerpt']; ?></p>
                                </div>

                                <div class="actions d-flex align-items-center mt-2 border-top pt-2">
                                    <?php $activated = in_array($namespace, smartpay_get_activated_integrations()); ?>
                                    <?php if (smartpay_integration_is_installed($integration)) : ?>
                                        <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" class="custom-control-input" id="<?php echo 'integration_' . $namespace ?>" data-namespace="<?php echo $namespace ?>" <?php echo $activated ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="<?php echo 'integration_' . $namespace ?>">
                                            </label>
                                        </div>

                                        <span class="ml-2 integration-status">
                                            <?php echo $activated ? 'Activated' : 'Disabled'; ?>
                                        </span>
                                    <?php else : ?>
                                        <?php smartpay_integration_get_not_installed_message($integration['type']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php wp_nonce_field('smartpay_integrations_toggle_activation', 'smartpay_integrations_toggle_activation'); ?>
    </div>

</div>