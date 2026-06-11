<?php defined('ABSPATH') || exit; ?>
<div class="smartpay" style="margin: 0 auto;">
    <div class="smartpay-form-shortcode smartpay-payment">
        <div class="card form bg-transparent border-0">
            <div class="card-body smartpay_form_builder_wrapper">
				<?php
				$smartpay_form = $smartpay_view_data['form'] ?? null;
				do_action( 'before_smartpay_payment_form', $smartpay_form );
				?>

				<?php
				$smartpay_goal_progress = smartpay_calculate_goal_progress( $smartpay_form->id ?? 0 );
				$smartpay_form_settings = is_string( $smartpay_form->settings ?? null ) ? json_decode( $smartpay_form->settings, true ) : ( $smartpay_form->settings ?? [] );
				$smartpay_goal           = $smartpay_form_settings['goal'] ?? [];
				?>
				<?php if ( ! empty( $smartpay_goal['enabled'] ) ) : ?>
					<?php
					$smartpay_show_to_public = $smartpay_goal['showToPublic'] ?? true;
					if ( $smartpay_show_to_public ) :
						$smartpay_current     = $smartpay_goal_progress['current'];
						$smartpay_target      = $smartpay_goal_progress['target'];
						$smartpay_percentage  = $smartpay_goal_progress['percentage'];
						$smartpay_goal_reached = $smartpay_goal_progress['goal_reached'];
						?>
						<div class="smartpay-goal-progress" style="margin-bottom: 20px; padding: 16px; background: #f8f9fa; border-radius: 8px; text-align: left;">
							<?php if ( $smartpay_goal_reached ) : ?>
								<p style="margin: 0 0 12px; font-weight: 600; color: #28a745;">
									<?php echo esc_html( $smartpay_goal['goalMetMessage'] ?? __( 'Goal reached!', 'smartpay' ) ); ?>
								</p>
							<?php else : ?>
								<?php $smartpay_type_label = ( $smartpay_goal['type'] ?? 'quantity' ) === 'quantity' ? _n( 'sold', 'sold', floor( $smartpay_current ), 'smartpay' ) : __( 'raised', 'smartpay' ); ?>
								<p style="margin: 0 0 8px; font-size: 14px; color: #555;">
									<strong><?php echo esc_html( number_format( $smartpay_current ) ); ?></strong> / <?php echo esc_html( number_format( $smartpay_target ) ); ?> <?php echo esc_html( $smartpay_type_label ); ?>
								</p>
							<?php endif; ?>
							<div style="background: #e9ecef; border-radius: 4px; height: 12px; overflow: hidden;">
								<div style="width: <?php echo esc_attr( $smartpay_percentage ); ?>%; background: #28a745; height: 100%; border-radius: 4px; transition: width 0.3s ease;"></div>
							</div>
							<?php if ( ! $smartpay_goal_reached ) : ?>
								<p style="margin: 8px 0 0; font-size: 12px; color: #888; text-align: right;"><?php echo esc_html( $smartpay_percentage ); ?>%</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

                <form id="smartpay-payment-form" action="<?php echo esc_url(smartpay_get_payment_page_uri()); ?>" method="POST"
                      enctype="multipart/form-data">
                    <div id="form-response" class="mb-3"></div>
					<?php wp_nonce_field( 'smartpay_process_payment', 'smartpay_process_payment' ); ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
					echo do_blocks( $smartpay_form->body );
					?>
                    <div id="mobile-field"></div>

                    <div class="form--amount-section mb-3">
                        <label class="form-amounts--label d-block m-0 mb-2"><?php esc_html_e( 'Select an amount', 'smartpay' ) ?></label>
                        <div class="form-amounts">
                            <div class="form-plan-grid ">
								<?php foreach ( $smartpay_form->amounts as $smartpay_index => $smartpay_amount ) : ?>
									<?php $smartpay_billing_type = $smartpay_amount['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME; ?>

									<?php if ( $smartpay_billing_type == \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME ): ?>
                                        <label class="form-plan-card plan-amount <?php echo 0 === $smartpay_index ? 'selected' : '' ?>">
                                            <input type="radio" name="_form_amount" id="_form_amount_<?php echo
											esc_attr($smartpay_amount['key']); ?>" class="radio" value="<?php echo
											esc_attr($smartpay_amount['amount']); ?>" <?php echo 0 === $smartpay_index ? 'checked' : '' ?> />
                                            <span class="plan-details" aria-hidden="true">
                                        <span class="plan-type">
                                            <?php echo esc_html($smartpay_amount['label'] ? $smartpay_amount['label'] : ''); ?>
                                        </span>
                                        <span class="plan-cost">
                                            <?php echo esc_html(smartpay_amount_format( $smartpay_amount['amount'] )); ?>
                                        </span>
                                                <input type="hidden" name="_form_billing_type" id="_form_billing_type_<?php echo esc_attr($smartpay_amount['key']); ?>" value="<?php echo esc_attr($smartpay_billing_type); ?>">
                                        </label>
									<?php endif; ?>

									<?php if ( \SmartPay\Models\Payment::BILLING_TYPE_SUBSCRIPTION === $smartpay_billing_type ) : ?>

                                        <label class="form-plan-card plan-amount <?php echo 0 === $smartpay_index ? 'selected' : '' ?>">
                                            <input type="hidden" name="_form_amount_key"
                                                   value="<?php echo esc_attr($smartpay_amount['key'] ?? ''); ?>">
                                            <input type="radio" name="_form_amount" id="_form_amount_<?php echo
											esc_attr($smartpay_amount['key']); ?><?php echo
											esc_attr($smartpay_amount['key']); ?>" class="radio" value="<?php echo
											esc_attr($smartpay_amount['amount']); ?>" <?php echo 0 === $smartpay_index ? 'checked' : '' ?> />
                                            <span class="plan-details" aria-hidden="true">
                                        <span class="plan-type">
                                            <?php echo esc_html($smartpay_amount['label'] ? $smartpay_amount['label'] : ''); ?>
                                        </span>
                                        <span class="plan-cost">
                                            <?php echo esc_html(smartpay_amount_format( $smartpay_amount['amount'] )); ?>
                                            <span class="slash">/</span>
                                            <span class="plan-cycle"><?php echo esc_html($smartpay_amount['billing_period']); ?></span></span>
                                            <?php if ( isset( $smartpay_amount['total_billing_cycle'] ) && $smartpay_amount['total_billing_cycle'] > 0 ): ?>
                                                <span class="plan-additional-info">Billed <?php echo esc_html($smartpay_amount['total_billing_cycle']); ?> times</span>
                                            <?php endif; ?>

												<?php if ( isset( $smartpay_amount['additional_charge'] ) && $smartpay_amount['additional_charge'] > 0 ): ?>
                                                    <span class="plan-additional-info"> Additional Charge <?php echo esc_html($smartpay_amount['additional_charge'] . smartpay_get_currency_symbol()); ?></span>
												<?php endif; ?>
                                        </span>
                                            <input type="hidden" name="_form_billing_type" id="_form_billing_type_<?php echo esc_attr($smartpay_amount['key']); ?>" value="<?php echo esc_attr($smartpay_billing_type); ?>">

                                            <input type="hidden" name="_form_billing_period"
                                                   id="_form_billing_period_<?php echo esc_attr($smartpay_amount['key']); ?>"
                                                   value="<?php echo esc_attr($smartpay_amount['billing_period']); ?>">
                                        </label>
									<?php endif; ?>
								<?php endforeach; ?>
                            </div>

							<?php
							$smartpay_form_amounts   = $smartpay_form->amounts;
							$smartpay_default_amount = reset( $smartpay_form_amounts );
							?>
                            <input type="hidden" name="smartpay_form_billing_type"
                                   value="<?php echo esc_attr($smartpay_default_amount['billing_type'] ?? \SmartPay\Models\Payment::BILLING_TYPE_ONE_TIME) ?>">
                            <input type="hidden" name="smartpay_form_billing_period"
                                   value="<?php echo esc_attr($smartpay_default_amount['billing_period'] ?? \SmartPay\Models\Payment::BILLING_PERIOD_MONTHLY) ?>">

							<?php if ( $smartpay_form->settings['allowCustomAmount'] ) : ?>
                                <!-- // Allow custom payment -->
                                <div class="form-group custom-amount-wrapper m-0 ">
                                    <label for="smartpay_custom_amount" class="form-amounts--label d-block m-0 mb-2">
										<?php echo esc_html($smartpay_form->settings['customAmountLabel']); ?></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text px-3"
                                                  id="default-currency"><?php echo esc_html(smartpay_get_currency_symbol()) ?></span>
                                        </div>
                                        <input type="text" class="form-control form--custom-amount amount"
                                               id="smartpay_custom_amount" name="smartpay_form_amount"
                                               value="<?php echo esc_attr($smartpay_default_amount['amount']) ?>" placeholder="">
                                    </div>
                                </div>
							<?php else : ?>
								<?php $smartpay_form_amounts = $smartpay_form->amounts; ?>
                                <input type="hidden" class="form-control form--custom-amount amount"
                                       name="smartpay_form_amount" value="<?php echo esc_attr($smartpay_default_amount['amount']) ?>">
							<?php endif; ?>
                        </div>
                    </div>

                    <input type="hidden" name="smartpay_selected_amount_key"
                           value="<?php echo esc_attr($smartpay_form->amounts[0]['key'] ?? '') ?>">

                    <input type="hidden" name="smartpay_form_id" id="smartpay_form_id"
                           value="<?php echo esc_attr($smartpay_form->id ?? 0); ?>">

                    <input type="hidden" name="smartpay_is_custom_payment" id="smartpay_is_custom_payment" value="false">

					<?php $smartpay_gateways = smartpay_get_enabled_payment_gateways( true ); ?>
					<?php if ( count( $smartpay_gateways ) == 1 ) : ?>
						<?php $smartpay_gateways_index = array_keys( $smartpay_gateways ); ?>
                        <input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway"
                               value="<?php echo esc_html( reset( $smartpay_gateways_index ) ); ?>" checked>

                        <!-- // If it has multiple payment gateway -->
					<?php elseif ( count( $smartpay_gateways ) > 1 ) : ?>
                        <label class="payment-gateway--label"><?php esc_html_e( 'Select a payment method', 'smartpay' ); ?></label>
                        <div class="mb-4">

                            <div class="gateways m-0 justify-content-left d-flex">
								<?php foreach ( $smartpay_gateways as $smartpay_gateway_id => $smartpay_gateway ) : ?>
                                    <div class="gateway custom-control custom-radio <?php echo $smartpay_gateway_id == $smartpay_chosen_gateway ? 'selected' : '' ?>">
                                        <input type="radio" name="smartpay_gateway"
                                               id="<?php echo 'smartpay_gateway_' . esc_attr( $smartpay_gateway_id ); ?>"
                                               value="<?php echo esc_attr( $smartpay_gateway_id ) ?>" <?php echo checked( $smartpay_gateway_id, $smartpay_chosen_gateway, false ); ?>
                                               class="radio">
                                        <label for="<?php echo 'smartpay_gateway_' . esc_attr( $smartpay_gateway_id ); ?>"
                                               class="gateway--label custom-control-label">
                                            <img src="<?php echo esc_html( $smartpay_gateway['gateway_icon'] ); ?>"
                                                 alt="<?php echo esc_html( $smartpay_gateway['checkout_label'] ); ?>">
                                        </label>
                                    </div>
								<?php endforeach; ?>
                            </div>
                        </div>
					<?php else : ?>
						<?php $smartpay_has_payment_error = true; ?>
                        <div class="alert alert-danger"><?php esc_html_e( 'You must enable a payment gateway to proceed a payment.', 'smartpay' ); ?></div>
					<?php endif; ?>

					<?php do_action( 'before_smartpay_payment_form_button', $smartpay_form ); ?>

                    <button type="button"
                            class="btn btn-success btn-block btn-lg smartpay-form-pay-now" <?php if ( $smartpay_has_payment_error ) {
						echo 'disabled';
					} ?>><?php echo esc_html( ($smartpay_form->settings['payButtonLabel'] ?? '') ?: __('Pay Now', 'smartpay' )) ?></button>

					<?php do_action( 'after_smartpay_payment_form_button', $smartpay_form ); ?>
                </form>
				<?php do_action( 'after_smartpay_payment_form', $smartpay_form ); ?>
            </div>
        </div>
        <div id="payment-response" class="p-4 bg-light" style="display: none;"></div>
    </div>
</div>
