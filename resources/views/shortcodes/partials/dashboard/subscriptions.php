<?php
defined( 'ABSPATH' ) || exit;

/** @var int $sp_customer_id */

$sp_subscriptions    = smartpay_get_user_subscriptions( $sp_customer_id );
$sp_default_currency = function_exists( 'smartpay_get_option' ) ? (string) smartpay_get_option( 'currency', 'USD' ) : 'USD';
?>

<div class="dashboard-header">
	<div class="header-content">
		<div>
			<h1><?php esc_html_e( 'Subscriptions', 'smartpay' ); ?></h1>
			<p class="subtitle"><?php esc_html_e( 'Your recurring plans, most recent first.', 'smartpay' ); ?></p>
		</div>
	</div>
</div>

<?php if ( ! empty( $sp_subscriptions ) ) : ?>
	<div class="sp-dash-cards">
		<?php foreach ( $sp_subscriptions as $sp_sub ) : ?>
			<?php
			$sp_badge    = smartpay_dashboard_status_badge( (string) $sp_sub->status );
			$sp_currency = $sp_sub->currency ?: $sp_default_currency;
			$sp_expiry   = $sp_sub->expiration && '0000-00-00 00:00:00' !== $sp_sub->expiration
				? gmdate( 'M d, Y', strtotime( $sp_sub->expiration ) )
				: '—';
			$sp_times    = (int) $sp_sub->bill_times;
			?>
			<div class="sp-dash-card">
				<div class="sp-dash-card__head">
					<span class="sp-dash-card__title"><?php printf( /* translators: %d: subscription id. */ esc_html__( 'Subscription #%d', 'smartpay' ), (int) $sp_sub->id ); ?></span>
					<span class="sp-dash-badge <?php echo esc_attr( $sp_badge['class'] ); ?>"><?php echo esc_html( $sp_badge['label'] ); ?></span>
				</div>

				<div class="sp-dash-card__price">
					<?php echo esc_html( smartpay_amount_format( $sp_sub->recurring_amount, $sp_currency ) ); ?>
					<span class="sp-dash-card__period">/ <?php echo esc_html( $sp_sub->period ?: __( 'cycle', 'smartpay' ) ); ?></span>
				</div>

				<div class="sp-dash-card__meta">
					<div class="sp-dash-card__row">
						<span><?php esc_html_e( 'Started', 'smartpay' ); ?></span>
						<strong><?php echo esc_html( gmdate( 'M d, Y', strtotime( $sp_sub->created_at ) ) ); ?></strong>
					</div>
					<div class="sp-dash-card__row">
						<span><?php esc_html_e( 'Next renewal', 'smartpay' ); ?></span>
						<strong><?php echo esc_html( $sp_expiry ); ?></strong>
					</div>
					<div class="sp-dash-card__row">
						<span><?php esc_html_e( 'Initial amount', 'smartpay' ); ?></span>
						<strong><?php echo esc_html( smartpay_amount_format( $sp_sub->initial_amount, $sp_currency ) ); ?></strong>
					</div>
					<div class="sp-dash-card__row">
						<span><?php esc_html_e( 'Billing cycles', 'smartpay' ); ?></span>
						<strong><?php echo $sp_times > 0 ? esc_html( $sp_times ) : esc_html__( 'Until cancelled', 'smartpay' ); ?></strong>
					</div>
					<div class="sp-dash-card__row">
						<span><?php esc_html_e( 'Payment method', 'smartpay' ); ?></span>
						<strong><?php echo esc_html( ucfirst( (string) $sp_sub->gateway ) ?: '—' ); ?></strong>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="sp-dash-panel">
		<div class="sp-dash-empty">
			<p><?php esc_html_e( 'You have no subscriptions yet.', 'smartpay' ); ?></p>
		</div>
	</div>
<?php endif; ?>
