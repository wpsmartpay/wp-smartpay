<?php
defined('ABSPATH') || exit;

use SmartPay\Models\Customer;

$smartpay_customer = is_user_logged_in() ? Customer::where('user_id', get_current_user_id())->first() : null;

$smartpay_gateways = smartpay_get_enabled_payment_gateways(true);

$smartpay_manual_gateways = smartpay_payment_gateways();
$smartpay_free_gateway = $smartpay_manual_gateways['free'];
// phpcs:ignore: WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
$smartpay_gateway_input = \sanitize_text_field(wp_unslash($_REQUEST['gateway'] ?? ''));

$smartpay_chosen_gateway = isset($smartpay_gateway_input) && smartpay_is_gateway_active($smartpay_gateway_input) ? $smartpay_gateway_input : smartpay_get_default_gateway();
$smartpay_has_payment_error = false;

$smartpay_product = $smartpay_view_data['product'] ?? null;
$smartpay_form = $smartpay_view_data['form'] ?? null;

// Effective price: when parent sale_price is 0 but paid variations exist,
// use the first paid variation's price so gateways render instead of hiding.
$smartpay_effective_price = (float) ($smartpay_product->sale_price ?? 0);
if ($smartpay_effective_price <= 0 && $smartpay_product && count($smartpay_product->variations ?? [])) {
    foreach ($smartpay_product->variations as $_var) {
        if ((float) $_var->sale_price > 0) {
            $smartpay_effective_price = (float) $_var->sale_price;
            break;
        }
    }
}
?>

<div class="modal fade payment-modal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header mx-auto">
                <button class="btn back-to-first-step">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <div class="d-flex flex-column justify-content-center modal-title">
                    <p class="payment-modal--small-title mb-2 text-capitalize"><?php echo esc_html($smartpay_product->title ?? $smartpay_form->title ?? 'Product/Form'); ?></p>
                    <h2 class="payment-modal--title amount m-0">--</h2>
                    <p class="sp-tax-amount-note m-0 text-muted" style="display:none;"></p>
                </div>


                <button class="btn modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="payment-modal--errors text-center" style="display: none"></div>

            <?php do_action('smartpay_product_modal_popup_content', $smartpay_product); ?>

            <div class="modal-body p-1 text-center step-1">
                <div class="align-self-center w-100">
                    <form action="<?php echo esc_url(smartpay_get_payment_page_uri()); ?>" method="POST">
                        <?php wp_nonce_field('smartpay_process_payment', 'smartpay_process_payment'); ?>
                        <div class="payment-modal--gateway">
                            <?php if ($smartpay_effective_price <= 0) : ?>
                                <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway" value="free" checked>

                            <?php elseif (count($smartpay_gateways) === 0) : ?>
                                <?php $smartpay_has_payment_error = true; ?>
                                <div class="alert alert-danger"><?php echo esc_html__('You must enable a payment gateway to proceed a payment.', 'smartpay'); ?></div>

                            <?php elseif (count($smartpay_gateways) === 1) : ?>
                                <?php $smartpay_gateways_index = array_keys($smartpay_gateways); ?>
                                <p class="payment-gateway--label text-muted single-gateway">
                                    <?php echo wp_kses_post(sprintf(__('Payment method - ', 'smartpay') . ' <strong>%s</strong>', esc_html(reset($smartpay_gateways)['checkout_label']))); ?>
                                </p>
                                <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway" value="<?php echo esc_attr(reset($smartpay_gateways_index)); ?>" checked>

                            <?php else : ?>
                                <?php
                                // Build the default gateway UI — a plain icon-only grid.
                                // Pro plugin can replace this via the filter with accordion/tab layout.
                                ob_start();
                                ?>
                                <div class="gateways m-0 justify-content-center d-flex">
                                    <?php foreach ($smartpay_gateways as $smartpay_gateway_id => $smartpay_gateway) : ?>
                                        <div class="gateway">
                                            <input type="radio" class="d-none" name="smartpay_gateway" id="<?php echo 'smartpay_gateway_' . esc_attr($smartpay_gateway_id); ?>" value="<?php echo esc_attr($smartpay_gateway_id); ?>" <?php checked($smartpay_gateway_id, $smartpay_chosen_gateway); ?>>
                                            <label for="<?php echo 'smartpay_gateway_' . esc_attr($smartpay_gateway_id); ?>" class="gateway--label">
                                                <img src="<?php echo esc_url($smartpay_gateway['gateway_icon']); ?>" alt="<?php echo esc_attr($smartpay_gateway['checkout_label']); ?>">
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php
                                $smartpay_modal_gateway_html = ob_get_clean();
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped inside
                                echo apply_filters(
                                    'smartpay_product_modal_gateway_html',
                                    $smartpay_modal_gateway_html,
                                    $smartpay_gateways,
                                    $smartpay_chosen_gateway,
                                    $smartpay_product
                                );
                                ?>
                            <?php endif; ?>
                        </div>

                        <div class="payment-modal--user-info">
                            <div class="form-row">
                                <div class="col-sm-6 form-group">
                                    <input type="text" placeholder="First name" class="form-control" name="smartpay_first_name" id="smartpay_first_name" value="<?php echo esc_attr($smartpay_customer->first_name ?? ''); ?>" autocomplete="first_name" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <input type="text" placeholder="Last name" class="form-control" name="smartpay_last_name" id="smartpay_last_name" value="<?php echo esc_attr($smartpay_customer->last_name ?? ''); ?>" autocomplete="last_name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="Email address" class="form-control" name="smartpay_email" id="smartpay_email" value="<?php echo esc_attr($smartpay_customer->email ?? ''); ?>" autocomplete="email" required>
                            </div>
                            <div id="mobile-field"></div>

                            <?php do_action('smartpay_before_product_payment_form_button', $smartpay_product); ?>

                            <button type="button" class="btn btn-success btn-block btn-lg smartpay-form-pay-now" <?php if ($smartpay_has_payment_error) echo 'disabled'; ?>>
                                <?php echo esc_html__('Pay Now', 'smartpay'); ?>
                            </button>

                            <?php do_action('smartpay_after_product_payment_form_button', $smartpay_product); ?>
                        </div>
                    </form>
                </div>

            </div>

            <div class="modal-body p-1 text-center step-2">
                <div class="align-self-center">
                    <div class="mb-5">
                        <div class="alert alert-warning py-3">
                            <p class="m-0"><?php echo esc_html__('Don\'t close this window before completing payment!', 'smartpay'); ?></p>
                        </div>
                    </div>
                    <div class="dynamic-content">
                        <div class="spinner-border" style="width: 40px; height: 40px;">
                            <span class="sr-only"><?php echo esc_html__('Loading', 'smartpay'); ?>...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-loading justify-content-center align-items-center">
                <div class="spinner-border text-secondary" style="width: 40px; height: 40px;">
                    <span class="sr-only"><?php echo esc_html__('Loading', 'smartpay'); ?>...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!--<div id="smartpay_currency_symbol" data-value="$"></div>-->
