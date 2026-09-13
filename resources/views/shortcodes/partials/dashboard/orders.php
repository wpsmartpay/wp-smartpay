<?php
defined( 'ABSPATH' ) || exit;

/** @var int $sp_customer_id */

$sp_payments = smartpay_get_user_payments( $sp_customer_id );

$sp_type_labels = array(
	'product_purchase' => __( 'Product Purchase', 'smartpay' ),
	'form_payment'     => __( 'Form Payment', 'smartpay' ),
);
?>

<div class="dashboard-header">
	<div class="header-content">
		<div>
			<h1><?php esc_html_e( 'Orders', 'smartpay' ); ?></h1>
			<p class="subtitle"><?php esc_html_e( 'All your payments, most recent first.', 'smartpay' ); ?></p>
		</div>
	</div>
</div>

<div class="sp-dash-panel">
	<?php if ( ! empty( $sp_payments ) ) : ?>
		<div class="sp-dash-table-wrap">
			<table class="sp-dash-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Order', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Type', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Date', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Gateway', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Status', 'smartpay' ); ?></th>
						<th class="sp-dash-table__actions"><?php esc_html_e( 'Details', 'smartpay' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $sp_payments as $sp_payment ) : ?>
						<?php
						$sp_badge      = smartpay_dashboard_status_badge( (string) $sp_payment->status );
						$sp_type_label = $sp_type_labels[ $sp_payment->type ] ?? ucwords( str_replace( '_', ' ', (string) $sp_payment->type ) );
						$sp_detail_url = smartpay_dashboard_order_url( (int) $sp_payment->id );
						?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Order', 'smartpay' ); ?>"><strong>#<?php echo esc_html( $sp_payment->id ); ?></strong></td>
							<td data-label="<?php esc_attr_e( 'Type', 'smartpay' ); ?>"><?php echo esc_html( $sp_type_label ); ?></td>
							<td data-label="<?php esc_attr_e( 'Date', 'smartpay' ); ?>"><?php echo esc_html( gmdate( 'M d, Y · g:i A', strtotime( $sp_payment->created_at ) ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Gateway', 'smartpay' ); ?>"><?php echo esc_html( ucfirst( (string) $sp_payment->gateway ) ?: '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Amount', 'smartpay' ); ?>"><?php echo esc_html( smartpay_amount_format( $sp_payment->amount, $sp_payment->currency ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'smartpay' ); ?>"><span class="sp-dash-badge <?php echo esc_attr( $sp_badge['class'] ); ?>"><?php echo esc_html( $sp_badge['label'] ); ?></span></td>
							<td data-label="<?php esc_attr_e( 'Details', 'smartpay' ); ?>" class="sp-dash-table__actions">
								<a href="<?php echo esc_url( $sp_detail_url ); ?>" class="sp-dash-btn sp-dash-btn--sm"><?php esc_html_e( 'View', 'smartpay' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="sp-dash-empty">
			<p><?php esc_html_e( 'You have no orders yet.', 'smartpay' ); ?></p>
		</div>
	<?php endif; ?>
</div>
