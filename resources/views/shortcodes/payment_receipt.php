<?php
defined('ABSPATH') || exit;

use SmartPay\Modules\Frontend\Utilities\Downloader;

$payment = $smartpay_view_data['payment'];

$smartpay_additional_charge = $payment->data['additional_info']['additional_charge'] ?? 0;
$smartpay_total_count = $payment->data['additional_info']['total_billing_cycle'] ?? 0;

if ($payment) :

	// Status-aware banner + badge. The receipt must reflect the payment's real
	// status — never claim "Payment Successful" for a pending/failed payment.
	$smartpay_status = strtolower( (string) $payment->status );

	$smartpay_status_styles = array(
		\SmartPay\Models\Payment::COMPLETED  => array( 'icon' => '✓', 'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'title_color' => '#166534', 'text_color' => '#15803d', 'badge_bg' => '#dcfce7', 'badge_color' => '#166534', 'title' => __( 'Payment Successful', 'smartpay' ), 'note' => __( 'Thank you! Your payment has been received.', 'smartpay' ) ),
		\SmartPay\Models\Payment::PROCESSING => array( 'icon' => '⟳', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'title_color' => '#1e40af', 'text_color' => '#1d4ed8', 'badge_bg' => '#dbeafe', 'badge_color' => '#1e40af', 'title' => __( 'Payment Processing', 'smartpay' ), 'note' => __( 'Your payment is being processed. This page will update once it completes.', 'smartpay' ) ),
		\SmartPay\Models\Payment::PENDING    => array( 'icon' => '⏳', 'bg' => '#fffbeb', 'border' => '#fde68a', 'title_color' => '#92400e', 'text_color' => '#b45309', 'badge_bg' => '#fef3c7', 'badge_color' => '#92400e', 'title' => __( 'Payment Pending', 'smartpay' ), 'note' => __( 'Your payment is awaiting confirmation. We will email you once it is confirmed.', 'smartpay' ) ),
		\SmartPay\Models\Payment::FAILED     => array( 'icon' => '✕', 'bg' => '#fef2f2', 'border' => '#fecaca', 'title_color' => '#991b1b', 'text_color' => '#b91c1c', 'badge_bg' => '#fee2e2', 'badge_color' => '#991b1b', 'title' => __( 'Payment Failed', 'smartpay' ), 'note' => __( 'This payment did not go through. Please try again or contact support.', 'smartpay' ) ),
		\SmartPay\Models\Payment::REFUNDED   => array( 'icon' => '↩', 'bg' => '#f9fafb', 'border' => '#e5e7eb', 'title_color' => '#374151', 'text_color' => '#6b7280', 'badge_bg' => '#f3f4f6', 'badge_color' => '#374151', 'title' => __( 'Payment Refunded', 'smartpay' ), 'note' => __( 'This payment has been refunded.', 'smartpay' ) ),
	);

	// Abandoned/revoked and any unknown status share the neutral "failed-ish" look.
	$smartpay_status_ui = $smartpay_status_styles[ $smartpay_status ] ?? array(
		'icon'        => '•',
		'bg'          => '#f9fafb',
		'border'      => '#e5e7eb',
		'title_color' => '#374151',
		'text_color'  => '#6b7280',
		'badge_bg'    => '#f3f4f6',
		'badge_color' => '#374151',
		/* translators: %s: payment status label */
		'title'       => sprintf( __( 'Payment %s', 'smartpay' ), ucfirst( $smartpay_status ) ),
		'note'        => __( 'Here are your payment details.', 'smartpay' ),
	);
	?>

	<?php do_action('smartpay_before_payment_receipt', $payment); ?>

	<div style="max-width:560px;margin:0 auto;font-family:sans-serif;">

		<div style="background:<?php echo esc_attr( $smartpay_status_ui['bg'] ); ?>;border:1px solid <?php echo esc_attr( $smartpay_status_ui['border'] ); ?>;border-radius:8px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
			<span style="font-size:22px;color:<?php echo esc_attr( $smartpay_status_ui['title_color'] ); ?>;"><?php echo esc_html( $smartpay_status_ui['icon'] ); ?></span>
			<div>
				<p style="margin:0;font-weight:700;font-size:15px;color:<?php echo esc_attr( $smartpay_status_ui['title_color'] ); ?>;"><?php echo esc_html( $smartpay_status_ui['title'] ); ?></p>
				<p style="margin:4px 0 0;font-size:13px;color:<?php echo esc_attr( $smartpay_status_ui['text_color'] ); ?>;"><?php echo esc_html( $smartpay_status_ui['note'] ); ?></p>
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

			<?php if ( isset( $payment->data['additional_info'] ) && $payment->data['additional_info'] && ( $smartpay_additional_charge > 0 || $smartpay_total_count > 0 ) ) : ?>
				<tr style="border-bottom:1px solid #f3f4f6;">
					<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Subscription', 'smartpay' ); ?></td>
					<td style="padding:10px 0;font-weight:500;">
						<?php
						if ( $smartpay_additional_charge > 0 ) {
							/* translators: %s: formatted charge amount */
							echo esc_html( sprintf( __( 'Additional charge %s', 'smartpay' ), smartpay_amount_format( $smartpay_additional_charge ) ) . ', ' );
						}
						if ( $smartpay_total_count > 0 ) {
							/* translators: %d: number of billing cycles */
							echo esc_html( sprintf( __( 'Billed %d times', 'smartpay' ), $smartpay_total_count ) );
						}
						?>
					</td>
				</tr>
			<?php endif; ?>

			<tr style="border-bottom:1px solid #f3f4f6;">
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Gateway', 'smartpay' ); ?></td>
				<td style="padding:10px 0;font-weight:500;">
					<?php
					$smartpay_gateway_label = smartpay_payment_gateways()[ $payment->gateway ]['checkout_label'] ?? ucfirst( $payment->gateway );
					echo esc_html( 'Free' === $smartpay_gateway_label ? __( 'Free', 'smartpay' ) : $smartpay_gateway_label );
					?>
				</td>
			</tr>

			<tr>
				<td style="padding:10px 0;color:#6b7280;"><?php esc_html_e( 'Status', 'smartpay' ); ?></td>
				<td style="padding:10px 0;">
					<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;background:<?php echo esc_attr( $smartpay_status_ui['badge_bg'] ); ?>;color:<?php echo esc_attr( $smartpay_status_ui['badge_color'] ); ?>;">
						<?php echo esc_html( ucfirst( $payment->status ) ); ?>
					</span>
				</td>
			</tr>

			<?php do_action('smartpay_after_payment_receipt_data', $payment); ?>
		</table>

		<?php if ( strtolower( $payment->status ) === \SmartPay\Models\Payment::COMPLETED ) : ?>

			<?php if ( $payment->type === 'Product Purchase' ) : ?>
				<?php $smartpay_product = \SmartPay\Models\Product::find( intval( $payment['data']['product_id'] ) ) ?? null; ?>
				<?php $smartpay_external_link = $smartpay_product['settings']['externalLink'] ?? null; ?>
				<?php if ( $smartpay_product && $smartpay_external_link && $smartpay_external_link['allowExternalLink'] ) : ?>
					<div style="margin-top:20px;">
						<a href="<?php echo esc_url( $smartpay_product['settings']['externalLink']['link'] ); ?>" target="_blank"
							style="display:inline-block;background:#4f46e5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;">
							<?php echo esc_html( $smartpay_product['settings']['externalLink']['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<?php $smartpay_form = \SmartPay\Models\Form::find( intval( $payment['data']['form_id'] ) ) ?? null; ?>
				<?php $smartpay_external_link = $smartpay_form['settings']['externalLink'] ?? null; ?>
				<?php if ( $smartpay_form && $smartpay_external_link && $smartpay_external_link['allowExternalLink'] ) : ?>
					<div style="margin-top:20px;">
						<a href="<?php echo esc_url( $smartpay_form['settings']['externalLink']['link'] ); ?>" target="_blank"
							style="display:inline-block;background:#4f46e5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;">
							<?php echo esc_html( $smartpay_form['settings']['externalLink']['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>

		<?php endif; ?>

		<?php $smartpay_product_id = $payment->data['product_id'] ?? 0; ?>
		<?php $smartpay_product = \SmartPay\Models\Product::with( ['parent'] )->find( $smartpay_product_id ); ?>
		<?php if ( strtolower( $payment->status ) === \SmartPay\Models\Payment::COMPLETED && $smartpay_product && count( (array) $smartpay_product->files ) > 0 ) : ?>
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
						<?php foreach ( $smartpay_product->files as $smartpay_file ) : ?>
							<tr style="border-bottom:1px solid #f3f4f6;">
								<td style="padding:10px 0;"><?php echo esc_html( $smartpay_file['name'] ); ?></td>
								<td style="padding:10px 0;text-align:right;">
									<a href="<?php echo esc_url( smartpay()->make( Downloader::class )->getDownloadUrl( $smartpay_file['id'], $payment->id, $smartpay_product->id ) ); ?>"
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
			$smartpay_dashboard_page_id = smartpay_get_option( 'customer_dashboard_page' );
			if ( $smartpay_dashboard_page_id ) :
			?>
				<a href="<?php echo esc_url( get_permalink( $smartpay_dashboard_page_id ) ); ?>"
					style="display:inline-block;background:#4f46e5;color:#fff;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">
					<?php esc_html_e( 'View Dashboard', 'smartpay' ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>

<?php
endif;
