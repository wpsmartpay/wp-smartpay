<?php
defined( 'ABSPATH' ) || exit;

/** @var WP_User $sp_current_user */
/** @var int $sp_customer_id */

$sp_summary = smartpay_get_user_dashboard_summary( $sp_customer_id );
?>

<div class="dashboard-header">
	<div class="header-content">
		<div>
				<?php /* translators: %s: customer display name. */ ?>
				<h1><?php printf( esc_html__( 'Welcome back, %s', 'smartpay' ), esc_html( $sp_current_user->display_name ) ); ?></h1>
			<p class="subtitle"><?php esc_html_e( 'A quick overview of your account activity.', 'smartpay' ); ?></p>
		</div>
	</div>
</div>

<!-- Summary stats (real data) -->
<div class="sp-dash-stats">
	<div class="sp-dash-stat">
		<span class="sp-dash-stat__label"><?php esc_html_e( 'Total Orders', 'smartpay' ); ?></span>
		<span class="sp-dash-stat__value"><?php echo esc_html( number_format_i18n( $sp_summary['total_payments'] ) ); ?></span>
		<?php /* translators: %s: number of completed payments. */ ?>
		<span class="sp-dash-stat__meta"><?php printf( esc_html__( '%s completed', 'smartpay' ), esc_html( number_format_i18n( $sp_summary['completed_payments'] ) ) ); ?></span>
	</div>
	<div class="sp-dash-stat">
		<span class="sp-dash-stat__label"><?php esc_html_e( 'Total Spent', 'smartpay' ); ?></span>
		<span class="sp-dash-stat__value"><?php echo esc_html( smartpay_amount_format( $sp_summary['total_spent'], $sp_summary['currency'] ) ); ?></span>
		<span class="sp-dash-stat__meta"><?php esc_html_e( 'Completed payments', 'smartpay' ); ?></span>
	</div>
	<div class="sp-dash-stat">
		<span class="sp-dash-stat__label"><?php esc_html_e( 'Active Subscriptions', 'smartpay' ); ?></span>
		<span class="sp-dash-stat__value"><?php echo esc_html( number_format_i18n( $sp_summary['active_subscriptions'] ) ); ?></span>
		<?php /* translators: %s: total number of subscriptions. */ ?>
		<span class="sp-dash-stat__meta"><?php printf( esc_html__( '%s total', 'smartpay' ), esc_html( number_format_i18n( $sp_summary['total_subscriptions'] ) ) ); ?></span>
	</div>
	<div class="sp-dash-stat">
		<span class="sp-dash-stat__label"><?php esc_html_e( 'Member Since', 'smartpay' ); ?></span>
		<span class="sp-dash-stat__value"><?php echo esc_html( gmdate( 'M Y', strtotime( $sp_current_user->user_registered ) ) ); ?></span>
		<span class="sp-dash-stat__meta"><?php echo esc_html( $sp_current_user->user_email ); ?></span>
	</div>
</div>

<!-- Recent orders -->
<div class="sp-dash-panel">
	<div class="sp-dash-panel__head">
		<h2><?php esc_html_e( 'Recent Orders', 'smartpay' ); ?></h2>
		<a href="<?php echo esc_url( smartpay_dashboard_view_url( 'orders' ) ); ?>" class="sp-dash-link"><?php esc_html_e( 'View all', 'smartpay' ); ?></a>
	</div>

	<?php if ( ! empty( $sp_summary['recent_payments'] ) ) : ?>
		<div class="sp-dash-table-wrap">
			<table class="sp-dash-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Order', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Date', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Status', 'smartpay' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $sp_summary['recent_payments'] as $sp_payment ) : ?>
						<?php $sp_badge = smartpay_dashboard_status_badge( (string) $sp_payment->status ); ?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Order', 'smartpay' ); ?>">#<?php echo esc_html( $sp_payment->id ); ?></td>
							<td data-label="<?php esc_attr_e( 'Date', 'smartpay' ); ?>"><?php echo esc_html( gmdate( 'M d, Y', strtotime( $sp_payment->created_at ) ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Amount', 'smartpay' ); ?>"><?php echo esc_html( smartpay_amount_format( $sp_payment->amount, $sp_payment->currency ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'smartpay' ); ?>"><span class="sp-dash-badge <?php echo esc_attr( $sp_badge['class'] ); ?>"><?php echo esc_html( $sp_badge['label'] ); ?></span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="sp-dash-empty">
			<p><?php esc_html_e( 'No orders yet.', 'smartpay' ); ?></p>
		</div>
	<?php endif; ?>
</div>
