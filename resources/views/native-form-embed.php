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

// Pay Button block — when present it owns the submit button's appearance. The
// block renders nothing inline (its save() returns null); we read its attributes
// here and render the real button after the gateway selector below, so the pay
// action always sits last regardless of where the block was placed.
$submit_btn = null;
if ( has_block( 'smartpay-form/submit-button', $post_id ) ) {
	foreach ( parse_blocks( $body ) as $sp_block ) {
		if ( 'smartpay-form/submit-button' === ( $sp_block['blockName'] ?? '' ) ) {
			$submit_btn = is_array( $sp_block['attrs'] ?? null ) ? $sp_block['attrs'] : array();
			break;
		}
	}
}
?>
<?php if ( ! empty( $goal['enabled'] ) && function_exists( 'smartpay_calculate_goal_progress' ) ) : ?>
	<?php
	$progress    = smartpay_calculate_goal_progress( (int) $post_id );
	$show_public = $goal['showToPublic'] ?? true;
	if ( $show_public ) :
		$current      = $progress['current'];
		$target       = $progress['target'];
		$percentage   = $progress['percentage'];
		$goal_reached = $progress['goal_reached'];
		?>
	<div class="smartpay-goal-progress" style="margin-bottom: 20px; padding: 16px; background: #f8f9fa; border-radius: 8px; text-align: left;">
		<?php if ( $goal_reached ) : ?>
			<p style="margin: 0 0 12px; font-weight: 600; color: #28a745;">
				<?php echo esc_html( $goal['goalMetMessage'] ?? __( 'Goal reached!', 'smartpay' ) ); ?>
			</p>
		<?php else : ?>
			<?php $type_label = ( $goal['type'] ?? 'quantity' ) === 'quantity' ? _n( 'sold', 'sold', floor( $current ), 'smartpay' ) : __( 'raised', 'smartpay' ); ?>
			<p style="margin: 0 0 8px; font-size: 14px; color: #555;">
				<strong><?php echo esc_html( number_format( $current ) ); ?></strong> / <?php echo esc_html( number_format( $target ) ); ?> <?php echo esc_html( $type_label ); ?>
			</p>
		<?php endif; ?>
		<div style="background: #e9ecef; border-radius: 4px; height: 12px; overflow: hidden;">
			<div style="width: <?php echo esc_attr( $percentage ); ?>%; background: #28a745; height: 100%; border-radius: 4px; transition: width 0.3s ease;"></div>
		</div>
		<?php if ( ! $goal_reached ) : ?>
			<p style="margin: 8px 0 0; font-size: 12px; color: #888; text-align: right;"><?php echo esc_html( $percentage ); ?>%</p>
		<?php endif; ?>
	</div>
	<?php endif; ?>
<?php endif; ?>

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
					// Render Gutenberg blocks (form fields).
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo do_blocks( $body );
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
						<label class="payment-gateway--label">
							<?php esc_html_e( 'Select a payment method', 'smartpay' ); ?>
						</label>
						<div class="mb-4">
							<div class="gateways m-0 justify-content-left d-flex">
								<?php foreach ( $gateways as $gw_id => $gateway ) : ?>
								<div class="gateway custom-control custom-radio <?php echo $gw_id === $chosen_gw ? 'selected' : ''; ?>">
									<input type="radio"
										name="smartpay_gateway"
										id="<?php echo 'smartpay_gateway_' . esc_attr( $gw_id ); ?>"
										value="<?php echo esc_attr( $gw_id ); ?>"
										<?php checked( $gw_id, $chosen_gw ); ?>
										class="radio" />
									<label for="<?php echo 'smartpay_gateway_' . esc_attr( $gw_id ); ?>"
										class="gateway--label custom-control-label">
										<img src="<?php echo esc_url( $gateway['gateway_icon'] ); ?>"
											alt="<?php echo esc_attr( $gateway['checkout_label'] ); ?>" />
									</label>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
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
		<div id="payment-response" class="p-4 bg-light" style="display: none;"></div>
	</div>
</div>
