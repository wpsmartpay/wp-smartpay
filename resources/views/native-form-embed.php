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

					<?php if ( ! empty( $amounts ) ) : ?>
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

					<button type="button"
						class="btn btn-success btn-block btn-lg smartpay-form-pay-now"
						<?php echo $has_payment_error ? 'disabled' : ''; ?>>
						<?php echo esc_html( $pay_button_label ); ?>
					</button>

					<?php do_action( 'after_smartpay_payment_form_button', (object) array( 'id' => $post_id ) ); ?>

				</form>
				<?php do_action( 'after_smartpay_payment_form', (object) array( 'id' => $post_id ) ); ?>
			</div>
		</div>
		<div id="payment-response" class="p-4 bg-light" style="display: none;"></div>
	</div>
</div>
