<?php

use SmartPay\Modules\Frontend\Utilities\Downloader;

$additional_charge = $payment->data['additional_info']['additional_charge'] ?? 0;
$total_count = $payment->data['additional_info']['total_billing_cycle'] ?? 0;

if ($payment) : ?>

	<?php do_action('smartpay_before_payment_receipt', $payment); ?>

	<div style="max-width:560px;margin:0 auto;font-family:sans-serif;">

		<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
			<span style="font-size:22px;">✓</span>
			<div>
				<p style="margin:0;font-weight:700;font-size:15px;color:#166534;"><?php esc_html_e( 'Payment Successful', 'smartpay' ); ?></p>
				<p style="margin:4px 0 0;font-size:13px;color:#15803d;"><?php esc_html_e( 'Thank you! Your payment has been received.', 'smartpay' ); ?></p>
			</div>
		</div>

		<table style="width:100%;border-collapse:collapse;font-size:14px;">
			<?php do_action('smartpay_before_payment_receipt_data', $payment); ?>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;width:40%;"><?php esc_html_e( 'Payment ID', 'smartpay' ); ?></td>
				<td style="padding:10px 0;font-weight:500;"><?php echo esc_html( $payment->id ); ?></td>
			</tr>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;"><?php echo esc_html( $payment->type === 'Product Purchase' ? __( 'Product', 'smartpay' ) : __( 'Form', 'smartpay' ) ); ?></td>
				<td style="padding:10px 0;font-weight:500;">
					<a style="color:#4f46e5;" href="<?php echo esc_url( smartpay_get_payment_product_or_form_name( $payment->id )['preview'] ); ?>" target="_blank">
						<?php echo esc_html( smartpay_get_payment_product_or_form_name( $payment->id )['name'] ); ?>
					</a>
				</td>
			</tr>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Name', 'smartpay' ); ?></td>
				<td style="padding:10px 0;font-weight:500;"><?php echo esc_html( $payment->customer->first_name . ' ' . $payment->customer->last_name ); ?></td>
			</tr>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Email', 'smartpay' ); ?></td>
				<td style="padding:10px 0;font-weight:500;"><?php echo esc_html( $payment->email ); ?></td>
			</tr>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Amount', 'smartpay' ); ?></td>
				<td style="padding:10px 0;font-weight:600;font-size:16px;"><?php echo esc_html( smartpay_amount_format( $payment->amount ) ); ?></td>
			</tr>

			<?php if ( isset( $payment->data['additional_info'] ) && $payment->data['additional_info'] && ( $additional_charge > 0 || $total_count > 0 ) ) : ?>
				<tr style="border-bottom:1px solid #f3f4f6;">
					<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Subscription', 'smartpay' ); ?></td>
					<td style="padding:10px 0;font-weight:500;">
						<?php
						if ( $additional_charge > 0 ) {
							/* translators: %s: formatted charge amount */
							echo esc_html( sprintf( __( 'Additional charge %s', 'smartpay' ), smartpay_amount_format( $additional_charge ) ) . ', ' );
						}
						if ( $total_count > 0 ) {
							/* translators: %d: number of billing cycles */
							echo esc_html( sprintf( __( 'Billed %d times', 'smartpay' ), $total_count ) );
						}
						?>
					</td>
				</tr>
			<?php endif; ?>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Gateway', 'smartpay' ); ?></td>
				<td style="padding:10px 0;font-weight:500;">
					<?php
					$gateway_label = smartpay_payment_gateways()[ $payment->gateway ]['checkout_label'] ?? ucfirst( $payment->gateway );
					echo esc_html( 'Free' === $gateway_label ? __( 'Free', 'smartpay' ) : $gateway_label );
					?>
				</td>
			</tr>

			<tr>
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Status', 'smartpay' ); ?></td>
				<td style="padding:10px 0;">
					<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#dcfce7;color:#166534;">
						<?php echo esc_html( ucfirst( $payment->status ) ); ?>
					</span>
				</td>
			</tr>

			<?php do_action('smartpay_after_payment_receipt_data', $payment); ?>
		</table>

		<?php if ( strtolower( $payment->status ) === \SmartPay\Models\Payment::COMPLETED ) : ?>

			<?php if ( $payment->type === 'Product Purchase' ) : ?>
				<?php $product = \SmartPay\Models\Product::find( intval( $payment['data']['product_id'] ) ) ?? null; ?>
				<?php $external_link = $product['settings']['externalLink'] ?? null; ?>
				<?php if ( $product && $external_link && $external_link['allowExternalLink'] ) : ?>
					<div style="margin-top:20px;">
						<a href="<?php echo esc_url( $product['settings']['externalLink']['link'] ); ?>" target="_blank"
							style="display:inline-block;background:#4f46e5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;">
							<?php echo esc_html( $product['settings']['externalLink']['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<?php $form = \SmartPay\Models\Form::find( intval( $payment['data']['form_id'] ) ) ?? null; ?>
				<?php $external_link = $form['settings']['externalLink'] ?? null; ?>
				<?php if ( $form && $external_link && $external_link['allowExternalLink'] ) : ?>
					<div style="margin-top:20px;">
						<a href="<?php echo esc_url( $form['settings']['externalLink']['link'] ); ?>" target="_blank"
							style="display:inline-block;background:#4f46e5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;">
							<?php echo esc_html( $form['settings']['externalLink']['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>

		<?php endif; ?>

		<?php $productId = $payment->data['product_id'] ?? 0; ?>
		<?php $product = \SmartPay\Models\Product::with( ['parent'] )->find( $productId ); ?>
		<?php if ( strtolower( $payment->status ) === \SmartPay\Models\Payment::COMPLETED && $product && count( $product->files ) > 0 ) : ?>
			<div style="margin-top:24px;">
				<h3 style="font-size:14px;font-weight:700;margin:0 0 12px;color:#111827;"><?php esc_html_e( 'Downloads', 'smartpay' ); ?></h3>
				<table style="width:100%;border-collapse:collapse;font-size:14px;">
					<thead>
						<tr style="border-bottom:2px solid #e5e7eb;">
							<th style="padding:8px 0;text-align:left;color:#6b7280;font-weight:600;"><?php esc_html_e( 'File', 'smartpay' ); ?></th>
							<th style="padding:8px 0;text-align:right;color:#6b7280;font-weight:600;"><?php esc_html_e( 'Action', 'smartpay' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $product->files as $file ) : ?>
							<tr style="border-bottom:1px solid #f3f4f6;">
								<td style="padding:10px 0;"><?php echo esc_html( $file['name'] ); ?></td>
								<td style="padding:10px 0;text-align:right;">
									<a href="<?php echo esc_url( smartpay()->make( Downloader::class )->getDownloadUrl( $file['id'], $payment->id, $product->id ) ); ?>"
										style="color:#4f46e5;text-decoration:none;font-weight:600;">
										<?php esc_html_e( 'Download', 'smartpay' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php do_action('smartpay_after_payment_receipt', $payment); ?>
		<?php do_action('smartpay_payment_' . $payment->gateway . '_receipt', $payment); ?>

		<div style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;display:flex;gap:12px;flex-wrap:wrap;">
			<a href="<?php echo esc_url( home_url() ); ?>"
				style="display:inline-block;background:#f9fafb;border:1px solid #e5e7eb;color:#374151;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">
				← <?php esc_html_e( 'Return to Home', 'smartpay' ); ?>
			</a>
			<?php
			$dashboard_page_id = smartpay_get_option( 'customer_dashboard_page' );
			if ( $dashboard_page_id ) :
			?>
				<a href="<?php echo esc_url( get_permalink( $dashboard_page_id ) ); ?>"
					style="display:inline-block;background:#4f46e5;color:#fff;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">
					<?php esc_html_e( 'View Dashboard', 'smartpay' ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>

<?php
endif;
