<?php
defined( 'ABSPATH' ) || exit;
/**
 * Embed template for [sp_form] shortcode.
 *
 * Variables injected by NativeForm::render_shortcode():
 *
 *   @var int    $post_id  Post ID of the smartpay_form CPT.
 *   @var array  $amounts  Decoded _smartpay_amounts array.
 *   @var array  $settings Decoded _smartpay_settings array.
 *   @var string $body     Post content (Gutenberg blocks markup).
 *   @var array  $atts     Shortcode attributes (includes 'behavior').
 *
 * @package SmartPay
 */

$pay_button_label    = $settings['pay_button_label'] ?? __( 'Pay Now', 'smartpay' );
$allow_custom_amount = ! empty( $settings['allow_custom_amount'] );
$custom_amount_label = $settings['custom_amount_label'] ?? __( 'Enter custom amount', 'smartpay' );

$gateways   = smartpay_get_enabled_payment_gateways( true );
$default_gw = smartpay_get_default_gateway();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$chosen_gw = isset( $_REQUEST['gateway'] ) && smartpay_is_gateway_active( sanitize_text_field( wp_unslash( $_REQUEST['gateway'] ) ) )
	? sanitize_text_field( wp_unslash( $_REQUEST['gateway'] ) )
	: $default_gw;

$goal              = $settings['goal'] ?? array();
$has_payment_error = empty( $gateways );
$default_amount    = reset( $amounts );

// When a Pricing block is present it renders its own amount cards inline (at the
// block's position in the body), so the template skips its own amount section to
// avoid duplicate cards. The block emits the same markup + field names.
$has_pricing_block = has_block( 'smartpay-form/pricing', $post_id );

// Submit Button block — the Pay Button child owns the button's appearance. All
// children render nothing inline (save() returns null); we read the pay child's
// attributes here and render the real button after the gateway selector below,
// so the pay action always sits last. Null → fall back to the legacy button.
$submit_btn = smartpay_get_submit_child_attrs( (int) $post_id, 'smartpay-form/submit-pay' );

// Reset the per-render flag for the #payment-response error container. Pro's
// Frontend module renders the canonical container inside the form (and sets
// this flag); the legacy fallback below only renders when no one else did —
// otherwise the page ends up with two elements sharing the same id.
$GLOBALS['smartpay_payment_response_rendered'] = false;

?>

<div class="smartpay">
	<div class="smartpay-form-shortcode smartpay-payment">
		<div class="card form bg-transparent border-0">
			<div class="card-body smartpay_form_builder_wrapper">
				<?php do_action( 'before_smartpay_payment_form', (object) array( 'id' => $post_id ) ); ?>
				<form id="smartpay-payment-form"
					action="<?php echo esc_url( smartpay_get_payment_page_uri() ); ?>"
					method="POST"
					enctype="multipart/form-data">

					<div id="form-response" class="mb-3"></div>
					<?php wp_nonce_field( 'smartpay_process_payment', 'smartpay_process_payment' ); ?>

					<?php
					// Render Gutenberg blocks (form fields). Set the form-render
					// context so dynamic blocks (Goal Progress) can resolve their
					// owning form, since the global post here is the host page.
					smartpay_current_form_render_id( (int) $post_id );
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo do_blocks( $body );
					smartpay_current_form_render_id( 0 );
					?>

					<div id="mobile-field"></div>

					<?php if ( ! empty( $amounts ) && ! $has_pricing_block ) : ?>
					<div class="form--amount-section mb-3">
						<label class="form-amounts--label d-block m-0 mb-2">
							<?php esc_html_e( 'Select an amount', 'smartpay' ); ?>
						</label>
						<div class="form-amounts">
							<div class="form-plan-grid">
								<?php foreach ( $amounts as $index => $amount ) : ?>
									<?php $billing_type = $amount['billing_type'] ?? 'One Time'; ?>
									<label class="form-plan-card plan-amount <?php echo 0 === $index ? 'selected' : ''; ?>">
										<input type="radio"
											name="_form_amount"
											id="_form_amount_<?php echo esc_attr( $amount['key'] ); ?>"
											class="radio"
											value="<?php echo esc_attr( $amount['amount'] ); ?>"
											<?php echo 0 === $index ? 'checked' : ''; ?> />
										<span class="plan-details" aria-hidden="true">
											<span class="plan-type">
												<?php echo esc_html( $amount['label'] ?? '' ); ?>
											</span>
											<span class="plan-cost">
												<?php echo esc_html( smartpay_amount_format( $amount['amount'] ) ); ?>
												<?php if ( 'One Time' !== $billing_type && ! empty( $amount['billing_period'] ) ) : ?>
													<span class="slash">/</span>
													<span class="plan-cycle"><?php echo esc_html( $amount['billing_period'] ); ?></span>
												<?php endif; ?>
											</span>
										</span>
										<input type="hidden"
											name="_form_billing_type"
											id="_form_billing_type_<?php echo esc_attr( $amount['key'] ); ?>"
											value="<?php echo esc_attr( $billing_type ); ?>" />
										<?php if ( ! empty( $amount['billing_period'] ) ) : ?>
										<input type="hidden"
											name="_form_billing_period"
											id="_form_billing_period_<?php echo esc_attr( $amount['key'] ); ?>"
											value="<?php echo esc_attr( $amount['billing_period'] ); ?>" />
										<?php endif; ?>
									</label>
								<?php endforeach; ?>
							</div>

							<input type="hidden" name="smartpay_form_billing_type"
								value="<?php echo esc_attr( $default_amount['billing_type'] ?? 'One Time' ); ?>" />
							<input type="hidden" name="smartpay_form_billing_period"
								value="<?php echo esc_attr( $default_amount['billing_period'] ?? 'month' ); ?>" />

							<?php if ( $allow_custom_amount ) : ?>
							<div class="form-group custom-amount-wrapper m-0">
								<label for="smartpay_custom_amount_<?php echo esc_attr( $post_id ); ?>"
									class="form-amounts--label d-block m-0 mb-2">
									<?php echo esc_html( $custom_amount_label ); ?>
								</label>
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text px-3" id="default-currency">
											<?php echo esc_html( smartpay_get_currency_symbol() ); ?>
										</span>
									</div>
									<input type="text"
										class="form-control form--custom-amount amount"
										id="smartpay_custom_amount_<?php echo esc_attr( $post_id ); ?>"
										name="smartpay_form_amount"
										value="<?php echo esc_attr( $default_amount['amount'] ?? '0.00' ); ?>"
										placeholder="" />
								</div>
							</div>
							<?php else : ?>
							<input type="hidden"
								class="form-control form--custom-amount amount"
								name="smartpay_form_amount"
								value="<?php echo esc_attr( $default_amount['amount'] ?? '0.00' ); ?>" />
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>

					<input type="hidden" name="smartpay_selected_amount_key"
						value="<?php echo esc_attr( $amounts[0]['key'] ?? '' ); ?>" />
					<input type="hidden" name="smartpay_form_id" id="smartpay_form_id"
						value="<?php echo esc_attr( $post_id ); ?>" />
					<input type="hidden" name="smartpay_is_custom_payment" id="smartpay_is_custom_payment"
						value="false" />

					<?php if ( count( $gateways ) === 1 ) : ?>
						<?php $gw_keys = array_keys( $gateways ); ?>
						<input class="d-none" type="radio" name="smartpay_gateway" id="smartpay_gateway"
							value="<?php echo esc_attr( reset( $gw_keys ) ); ?>" checked />
					<?php elseif ( count( $gateways ) > 1 ) : ?>
						<?php ob_start(); ?>
						<label class="payment-gateway--label">
							<?php esc_html_e( 'Select a payment method', 'smartpay' ); ?>
						</label>
						<div class="mb-4">
							<div class="smartpay-gateways-accordion">
								<?php foreach ( $gateways as $gw_id => $gateway ) : ?>
								<div class="smartpay-gateway-card<?php echo $gw_id === $chosen_gw ? ' selected' : ''; ?>"
									data-gateway="<?php echo esc_attr( $gw_id ); ?>">
									<input type="radio"
										name="smartpay_gateway"
										id="<?php echo 'smartpay_gateway_' . esc_attr( $gw_id ); ?>"
										value="<?php echo esc_attr( $gw_id ); ?>"
										<?php checked( $gw_id, $chosen_gw ); ?>
										class="radio smartpay-gateway-card__radio" />
									<label for="<?php echo 'smartpay_gateway_' . esc_attr( $gw_id ); ?>"
										class="smartpay-gateway-card__head">
										<span class="smartpay-gateway-card__radiomark" aria-hidden="true"></span>
										<span class="smartpay-gateway-card__icon">
											<img src="<?php echo esc_url( $gateway['gateway_icon'] ); ?>"
												alt="<?php echo esc_attr( $gateway['checkout_label'] ); ?>" />
										</span>
										<span class="smartpay-gateway-card__label">
											<?php echo esc_html( $gateway['checkout_label'] ); ?>
										</span>
										<span class="smartpay-gateway-card__chevron" aria-hidden="true">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
										</span>
									</label>
									<div class="smartpay-gateway-card__body">
										<div class="smartpay-gateway-card__body-inner">
										<?php
										// Let gateways / add-ons inject inline checkout content
										// (instructions, fields, notices) under the selected gateway.
										// When nothing is injected, the default hint below explains
										// the redirect-style flow.
										do_action( 'smartpay_native_gateway_checkout_fields', $gw_id, $gateway, $post_id );
										?>
										<div class="smartpay-gateway-card__hint-box">
											<div class="smartpay-gateway-card__hint-title-row">
												<span class="smartpay-gateway-card__hint-logo">
													<img src="<?php echo esc_url( $gateway['gateway_icon'] ); ?>" alt="<?php echo esc_attr( $gateway['checkout_label'] ); ?>" />
												</span>
												<span class="smartpay-gateway-card__hint-title-text">
													<?php printf( esc_html__( '%s selected.', 'smartpay' ), esc_html( $gateway['checkout_label'] ) ); ?>
												</span>
											</div>
											<div class="smartpay-gateway-card__hint-divider"></div>
											<div class="smartpay-gateway-card__hint-redirect-row">
												<span class="smartpay-gateway-card__hint-redirect-icon" aria-hidden="true">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
														<rect x="2" y="4" width="16" height="15" rx="2" stroke="#94a3b8"></rect>
														<line x1="2" y1="9" x2="18" y2="9" stroke="#cbd5e1"></line>
														<path d="M14 14h6m0 0v-6m0 6l-6-6" stroke="#64748b" stroke-width="2"></path>
													</svg>
												</span>
												<span class="smartpay-gateway-card__hint-redirect-text">
													<?php esc_html_e( 'After submission, you will be redirected to securely complete next steps.', 'smartpay' ); ?>
												</span>
											</div>
										</div>
									</div>
								</div>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php
						$smartpay_gateway_html = ob_get_clean();
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped inside
						echo apply_filters( 'smartpay_form_gateway_layout_html', $smartpay_gateway_html, $gateways, $chosen_gw, $post_id );
						?>
					<?php else : ?>
						<?php $has_payment_error = true; ?>
						<div class="alert alert-danger">
							<?php esc_html_e( 'You must enable a payment gateway to proceed with payment.', 'smartpay' ); ?>
						</div>
					<?php endif; ?>

					<?php do_action( 'before_smartpay_payment_form_button', (object) array( 'id' => $post_id ) ); ?>

					<?php if ( is_array( $submit_btn ) ) : ?>
						<?php
						// Render the Pay Button block's configured button. Numerics are
						// cast; colours pass through sanitize + esc_attr; the icon SVG is
						// from a fixed server-side whitelist (no user input).
						$btn_full   = ! empty( $submit_btn['fullWidth'] );
						$btn_align  = in_array( $submit_btn['align'] ?? 'left', array( 'left', 'center', 'right' ), true ) ? $submit_btn['align'] : 'left';
						$btn_width  = absint( $submit_btn['width'] ?? 0 );
						$btn_bg     = sanitize_text_field( $submit_btn['bgColor'] ?? '#28a745' );
						$btn_text   = sanitize_text_field( $submit_btn['textColor'] ?? '#ffffff' );
						$btn_border = sanitize_text_field( $submit_btn['borderColor'] ?? '' );
						$btn_bw     = absint( $submit_btn['borderWidth'] ?? 0 );
						$btn_radius = absint( $submit_btn['borderRadius'] ?? 6 );
						$btn_fs     = absint( $submit_btn['fontSize'] ?? 16 );
						$btn_fw     = preg_replace( '/[^0-9a-z]/', '', (string) ( $submit_btn['fontWeight'] ?? '600' ) );
						$btn_py     = absint( $submit_btn['paddingY'] ?? 14 );
						$btn_px     = absint( $submit_btn['paddingX'] ?? 24 );

						// Icon: a custom media image/SVG, or a preset whitelist SVG.
						$btn_icon = '';
						if ( 'custom' === ( $submit_btn['iconType'] ?? 'preset' ) ) {
							$custom_icon_url = esc_url_raw( $submit_btn['customIconUrl'] ?? '' );
							if ( $custom_icon_url ) {
								$btn_icon = '<img src="' . esc_url( $custom_icon_url ) . '" alt="" class="smartpay-submit-icon-img" style="height:1.25em;width:auto;display:inline-block;" />';
							}
						} else {
							$btn_icon = smartpay_submit_button_icon_svg( sanitize_key( $submit_btn['icon'] ?? '' ) );
						}

						$btn_ipos   = 'right' === ( $submit_btn['iconPosition'] ?? 'left' ) ? 'right' : 'left';
						$btn_label  = $submit_btn['label'] ?? $pay_button_label;

						$btn_style  = 'display:inline-flex;align-items:center;justify-content:center;';
						$btn_style .= 'gap:' . ( $btn_icon ? '8px' : '0' ) . ';';
						$btn_style .= 'background:' . $btn_bg . ';color:' . $btn_text . ';';
						$btn_style .= 'border-radius:' . $btn_radius . 'px;';
						$btn_style .= 'font-size:' . $btn_fs . 'px;font-weight:' . $btn_fw . ';';
						$btn_style .= 'padding:' . $btn_py . 'px ' . $btn_px . 'px;line-height:1.2;cursor:pointer;';
						$btn_style .= $btn_bw > 0 ? 'border:' . $btn_bw . 'px solid ' . $btn_border . ';' : 'border:none;';
						if ( $btn_full ) {
							$btn_style .= 'width:100%;';
						} elseif ( $btn_width ) {
							$btn_style .= 'width:' . $btn_width . '%;';
						}
						$wrap_style = 'text-align:' . ( $btn_full ? 'left' : $btn_align ) . ';';
						?>
						<div class="smartpay-submit-button-wrap" style="<?php echo esc_attr( $wrap_style ); ?>">
							<button type="button"
								class="smartpay-form-pay-now"
								style="<?php echo esc_attr( $btn_style ); ?>"
								<?php echo $has_payment_error ? 'disabled' : ''; ?>>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Preset SVG is a fixed server-side whitelist; custom icon URL is esc_url()d at build above.
								echo 'left' === $btn_ipos ? $btn_icon : '';
								?>
								<span><?php echo esc_html( $btn_label ); ?></span>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Preset SVG is a fixed server-side whitelist; custom icon URL is esc_url()d at build above.
								echo 'right' === $btn_ipos ? $btn_icon : '';
								?>
							</button>
						</div>
					<?php else : ?>
						<button type="button"
							class="btn btn-success btn-block btn-lg smartpay-form-pay-now"
							<?php echo $has_payment_error ? 'disabled' : ''; ?>>
							<?php echo esc_html( $pay_button_label ); ?>
						</button>
					<?php endif; ?>

					<?php do_action( 'after_smartpay_payment_form_button', (object) array( 'id' => $post_id ) ); ?>

				</form>
				<?php do_action( 'after_smartpay_payment_form', (object) array( 'id' => $post_id ) ); ?>
			</div>
		</div>
		<?php if ( empty( $GLOBALS['smartpay_payment_response_rendered'] ) ) : ?>
			<div id="payment-response" class="p-4 bg-light" style="display: none;"></div>
		<?php endif; ?>
	</div>
</div>
