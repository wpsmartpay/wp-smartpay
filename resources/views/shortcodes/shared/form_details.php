<div class="smartpay" style="margin: 0 auto;">
    <div class="smartpay-form-shortcode smartpay-payment">
        <div class="card form bg-transparent border-0">
            <div class="card-body smartpay_form_builder_wrapper">
				<?php do_action( 'before_smartpay_payment_form', $form ); ?>
                <form id="smartpay-payment-form" action="<?php echo esc_url(smartpay_get_payment_page_uri()); ?>" method="POST"
                      enctype="multipart/form-data">
                    <div id="form-response" class="mb-3"></div>
					<?php wp_nonce_field( 'smartpay_process_payment', 'smartpay_process_payment' ); ?>
					<?php echo do_blocks( $form->body );
					?>
                    <div id="mobile-field"></div>

                    <div class="form--amount-section mb-3">
                        <label class="form-amounts--label d-block m-0 mb-2"><?php esc_html_e( 'Select an amount', 'smartpay' ) ?></label>
                        <div class="form-amounts">
                            <div class="form-plan-grid ">
								<?php foreach ( $form->amounts as $index => $amount ) : ?>
									<?php $billingType = $amount['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>

									<?php if ( $billingType == \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME ): ?>
                                        <label class="form-plan-card plan-amount <?php echo 0 === $index ? 'selected' : '' ?>">
                                            <input type="radio" name="_form_amount" id="_form_amount_<?php echo
											esc_attr($amount['key']); ?>" class="radio" value="<?php echo
											esc_attr($amount['amount']); ?>" <?php echo 0 === $index ? 'checked' : '' ?> />
                                            <span class="plan-details" aria-hidden="true">
                                        <span class="plan-type">
                                            <?php echo esc_html($amount['label'] ? $amount['label'] : ''); ?>
                                        </span>
                                        <span class="plan-cost">
                                            <?php echo esc_html(smartpay_amount_format( $amount['amount'] )); ?>
                                        </span>
                                                <input type="hidden" name="_form_billing_type" id="_form_billing_type_<?php echo esc_attr($amount['key']); ?>" value="<?php echo esc_attr($billingType); ?>">
                                        </label>
									<?php endif; ?>

									<?php if ( \SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $billingType ) : ?>

                                        <label class="form-plan-card plan-amount <?php echo 0 === $index ? 'selected' : '' ?>">
                                            <input type="hidden" name="_form_amount_key"
                                                   value="<?php echo esc_attr($amount['key'] ?? ''); ?>">
                                            <input type="radio" name="_form_amount" id="_form_amount_<?php echo
											esc_attr($amount['key']); ?><?php echo
											esc_attr($amount['key']); ?>" class="radio" value="<?php echo
											esc_attr($amount['amount']); ?>" <?php echo 0 === $index ? 'checked' : '' ?> />
                                            <span class="plan-details" aria-hidden="true">
                                        <span class="plan-type">
                                            <?php echo esc_html($amount['label'] ? $amount['label'] : ''); ?>
                                        </span>
                                        <span class="plan-cost">
                                            <?php echo esc_html(smartpay_amount_format( $amount['amount'] )); ?>
                                            <span class="slash">/</span>
                                            <span class="plan-cycle"><?php echo esc_html($amount['billing_period']); ?></span></span>
                                            <?php if ( isset( $amount['total_billing_cycle'] ) && $amount['total_billing_cycle'] > 0 ): ?>
                                                <span class="plan-additional-info">Billed <?php echo esc_html($amount['total_billing_cycle']); ?> times</span>
                                            <?php endif; ?>

												<?php if ( isset( $amount['additional_charge'] ) && $amount['additional_charge'] > 0 ): ?>
                                                    <span class="plan-additional-info"> Additional Charge <?php echo esc_html($amount['additional_charge'] . smartpay_get_currency_symbol()); ?></span>
												<?php endif; ?>
                                        </span>
                                            <input type="hidden" name="_form_billing_type" id="_form_billing_type_<?php echo esc_attr($amount['key']); ?>" value="<?php echo esc_attr($billingType); ?>">

                                            <input type="hidden" name="_form_billing_period"
                                                   id="_form_billing_period_<?php echo esc_attr($amount['key']); ?>"
                                                   value="<?php echo esc_attr($amount['billing_period']); ?>">
                                        </label>
									<?php endif; ?>
								<?php endforeach; ?>
                            </div>

							<?php
							$formAmounts   = $form->amounts;
							$defaultAmount = reset( $formAmounts );
							?>
                            <input type="hidden" name="smartpay_form_billing_type"
                                   value="<?php echo esc_attr($defaultAmount['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME) ?>">
                            <input type="hidden" name="smartpay_form_billing_period"
                                   value="<?php echo esc_attr($defaultAmount['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY) ?>">

							<?php if ( $form->settings['allowCustomAmount'] ) : ?>
                                <!-- // Allow custom payment -->
                                <div class="form-group custom-amount-wrapper m-0 ">
                                    <label for="smartpay_custom_amount" class="form-amounts--label d-block m-0 mb-2">
										<?php echo esc_html($form->settings['customAmountLabel']); ?></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text px-3"
                                                  id="default-currency"><?php echo esc_html(smartpay_get_currency_symbol()) ?></span>
                                        </div>
                                        <input type="text" class="form-control form--custom-amount amount"
                                               id="smartpay_custom_amount" name="smartpay_form_amount"
                                               value="<?php echo esc_attr($defaultAmount['amount']) ?>" placeholder="">
                                    </div>
                                </div>
							<?php else : ?>
								<?php $formAmounts = $form->amounts; ?>
                                <input type="hidden" class="form-control form--custom-amount amount"
                                       name="smartpay_form_amount" value="<?php echo esc_attr($defaultAmount['amount']) ?>">
							<?php endif; ?>
                        </div>
                    </div>

                    <input type="hidden" name="smartpay_selected_amount_key"
                           value="<?php echo esc_attr($form->amounts[0]['key'] ?? '') ?>">

                    <input type="hidden" name="smartpay_form_id" id="smartpay_form_id"
                           value="<?php echo esc_attr($form->id ?? 0); ?>">

                    <input type="hidden" name="smartpay_is_custom_payment" id="smartpay_is_custom_payment" value="false">

					<?php $gateways = smartpay_get_enabled_payment_gateways( true ); ?>
					<?php if ( count( $gateways ) == 1 ) : ?>
						<?php $gateways_index = array_keys( $gateways ); ?>
                        <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway"
                               value="<?php echo esc_html( reset( $gateways_index ) ); ?>" checked>

                        <!-- // If it has multiple payment gateway -->
					<?php elseif ( count( $gateways ) > 1 ) : ?>
                        <label class="payment-gateway--label"><?php esc_html_e( 'Select a payment method', 'smartpay' ); ?></label>
                        <div class="mb-4">

                            <div class="gateways m-0 justify-content-left d-flex">
								<?php foreach ( $gateways as $gatewayId => $gateway ) : ?>
                                    <div class="gateway custom-control custom-radio <?php echo $gatewayId == $chosen_gateway ? 'selected' : '' ?>">
                                        <input type="radio" name="smartpay_gateway"
                                               id="<?php echo 'smartpay_gateway_' . esc_attr( $gatewayId ); ?>"
                                               value="<?php echo esc_attr( $gatewayId ) ?>" <?php echo checked( $gatewayId, $chosen_gateway, false ); ?>
                                               class="radio">
                                        <label for="<?php echo 'smartpay_gateway_' . esc_attr( $gatewayId ); ?>"
                                               class="gateway--label custom-control-label">
                                            <img src="<?php echo esc_html( $gateway['gateway_icon'] ); ?>"
                                                 alt="<?php echo esc_html( $gateway['checkout_label'] ); ?>">
                                        </label>
                                    </div>
								<?php endforeach; ?>
                            </div>
                        </div>
					<?php else : ?>
						<?php $has_payment_error = true; ?>
                        <div class="alert alert-danger"><?php esc_html_e( 'You must enable a payment gateway to proceed a payment.', 'smartpay' ); ?></div>
					<?php endif; ?>

					<?php do_action( 'before_smartpay_payment_form_button', $form ); ?>

                    <button type="button"
                            class="btn btn-success btn-block btn-lg smartpay-form-pay-now" <?php if ( $has_payment_error ) {
						echo 'disabled';
					} ?>><?php esc_html_e( $form['settings']['payButtonLabel'] ?: 'Pay Now', 'smartpay' ) ?></button>

					<?php do_action( 'after_smartpay_payment_form_button', $form ); ?>
                </form>
				<?php do_action( 'after_smartpay_payment_form', $form ); ?>
            </div>
        </div>
        <div id="payment-response" class="p-4 bg-light" style="display: none;"></div>
    </div>
</div>
