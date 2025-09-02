<div class="smartpay">
    <div class="wrap container">
        <h1 class="wp-heading-inline"></h1>
    </div>
    <div class="container smartpay-integrations">
        <div class="d-flex align-items-center justify-content-between py-1 px-4 mt-3 text-white bg-dark rounded-top">
            <div class="lh-100">
                <h2 class="text-white"><?php echo esc_html__('SmartPay - Integrations', 'smartpay'); ?></h2>
            </div>
            <div>
                <a class="btn btn-dark btn-sm" href="https://wpsmartpay.com/changelog/" target="_blank">v<?php echo esc_html(SMARTPAY_VERSION); ?></a>
            </div>
        </div>

        <div class="card border-light shadow-sm mt-0">
            <div class="card-body">
                <div class="row">
                    <?php foreach (smartpay_integrations() as $namespace => $integration) : ?>
                        <div class="col-lg-3 col-md-4 integration">
                            <div class="card p-3 mb-3 d-flex">
                                <div class="image m-0 mb-3 text-center" style="height: 90px;">
                                    <img src="<?php echo esc_url($integration['cover']); ?>" class="img-fluid" alt="">
                                </div>
                                <div class="info">
                                    <h3 class="name m-0 mb-1"><?php echo esc_html($integration['name']); ?></h3>
                                    <p class="excerpt mb-1"><?php echo wp_kses_post($integration['excerpt']); ?></p>
                                </div>

                                <div class="actions d-flex align-items-center mt-2 border-top pt-2">
                                    <?php $activated = in_array($namespace, smartpay_get_activated_integrations()); ?>
                                    <?php if (smartpay_integration_is_installed($integration)) : ?>
                                        <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" class="custom-control-input" id="<?php echo 'integration_' . esc_attr($namespace) ?>" data-namespace="<?php echo esc_attr($namespace) ?>" <?php echo $activated ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="<?php echo 'integration_' . esc_attr($namespace) ?>">
                                            </label>
                                        </div>
                                        <div class="d-flex bd-highlight">
                                            <span class="ml-2 integration-status p-2 bd-highlight">
                                                <?php echo $activated ? esc_html__('Activated', 'smartpay') : esc_html__('Disabled', 'smartpay'); ?>
                                            </span>
                                            <?php if (!empty($integration['setting_link']) && $activated == true) : ?>
                                                <span class="ml-auto p-2 bd-highlight">
                                                    <a href="<?php echo esc_url(site_url()) ?>/wp-admin/admin.php?page=smartpay-setting&<?php echo esc_url($integration['setting_link']) ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                                                            <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z" />
                                                        </svg>
                                                    </a>
                                                </span>
                                            <?php endif; ?>
                                        </div>
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
