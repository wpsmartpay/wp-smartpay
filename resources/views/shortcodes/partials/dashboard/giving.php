<?php
/**
 * Dashboard → Giving: the donor's own view of their gifts.
 *
 * @package SmartPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customer id of the logged-in donor, set by dashboard.php.
 *
 * @var int $sp_customer_id
 */

$sp_donor  = $sp_customer_id ? ( smartpay_query_donors(
	array(
		'customer_ids' => array( $sp_customer_id ),
		'per_page'     => 1,
	)
)['data'][0] ?? null ) : null;
$sp_giving = $sp_donor ? smartpay_get_donor_giving( $sp_customer_id ) : null;
$sp_types  = smartpay_donor_types();
$sp_active = $sp_giving ? array_values(
	array_filter(
		$sp_giving['monthly'],
		static function ( $sub ) {
			return in_array( $sub['status'], array( 'active', 'trialling' ), true );
		}
	)
) : array();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flash flag after the privacy form redirect.
$sp_saved = isset( $_GET['sp_privacy'] ) && 'saved' === sanitize_key( wp_unslash( $_GET['sp_privacy'] ) );
?>

<div class="dashboard-header">
	<div class="header-content">
		<div>
			<h1><?php esc_html_e( 'Your giving', 'smartpay' ); ?></h1>
			<p class="subtitle">
				<?php if ( $sp_donor ) : ?>
					<?php
					/* translators: %s: donor type, e.g. "Repeat" */
					printf( esc_html__( '%s donor', 'smartpay' ), esc_html( $sp_types[ $sp_donor['type'] ] ?? '' ) );
					?>
				<?php else : ?>
					<?php esc_html_e( 'Your donations will appear here.', 'smartpay' ); ?>
				<?php endif; ?>
			</p>
		</div>
	</div>
</div>

<?php if ( ! $sp_donor ) : ?>
	<div class="sp-dash-panel">
		<div class="sp-dash-empty"><p><?php esc_html_e( 'You haven’t made a donation yet.', 'smartpay' ); ?></p></div>
	</div>
	<?php
	return;
endif;
?>

<div class="sp-dash-cards">
	<div class="sp-dash-card">
		<div class="sp-dash-card__head"><span class="sp-dash-card__title"><?php esc_html_e( 'Lifetime given', 'smartpay' ); ?></span></div>
		<div class="sp-dash-card__price"><?php echo esc_html( smartpay_amount_format( $sp_giving['stats']['lifetime'] ) ); ?></div>
		<div class="sp-dash-card__meta"><div class="sp-dash-card__row"><span><?php esc_html_e( 'All campaigns', 'smartpay' ); ?></span></div></div>
	</div>
	<div class="sp-dash-card">
		<div class="sp-dash-card__head"><span class="sp-dash-card__title"><?php esc_html_e( 'Gifts', 'smartpay' ); ?></span></div>
		<div class="sp-dash-card__price"><?php echo esc_html( number_format_i18n( $sp_giving['stats']['gifts'] ) ); ?></div>
		<div class="sp-dash-card__meta"><div class="sp-dash-card__row"><span><?php esc_html_e( 'Completed', 'smartpay' ); ?></span></div></div>
	</div>
	<div class="sp-dash-card">
		<div class="sp-dash-card__head"><span class="sp-dash-card__title"><?php esc_html_e( 'Active monthly', 'smartpay' ); ?></span></div>
		<div class="sp-dash-card__price">
			<?php
			echo $sp_active
				? esc_html( smartpay_amount_format( array_sum( wp_list_pluck( $sp_active, 'amount' ) ) ) . ' / ' . __( 'month', 'smartpay' ) )
				: esc_html__( 'None', 'smartpay' );
			?>
		</div>
		<div class="sp-dash-card__meta"><div class="sp-dash-card__row"><span><?php esc_html_e( 'Give monthly from any campaign', 'smartpay' ); ?></span></div></div>
	</div>
	<div class="sp-dash-card">
		<div class="sp-dash-card__head"><span class="sp-dash-card__title"><?php esc_html_e( 'Donor since', 'smartpay' ); ?></span></div>
		<div class="sp-dash-card__price"><?php echo esc_html( date_i18n( 'M Y', strtotime( $sp_donor['donor_since'] ) ) ); ?></div>
		<div class="sp-dash-card__meta"><div class="sp-dash-card__row"><span><?php echo esc_html( $sp_donor['email'] ); ?></span></div></div>
	</div>
</div>

<?php if ( $sp_giving['monthly'] ) : ?>
	<div class="sp-dash-panel">
		<div class="sp-dash-panel__head"><h2><?php esc_html_e( 'Monthly gifts', 'smartpay' ); ?></h2></div>
		<div class="sp-dash-table-wrap">
			<table class="sp-dash-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Campaign', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Started', 'smartpay' ); ?></th>
						<th><?php esc_html_e( 'Status', 'smartpay' ); ?></th>
						<th class="sp-dash-table__actions"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $sp_giving['monthly'] as $sp_sub ) : ?>
						<?php $sp_badge = smartpay_dashboard_status_badge( $sp_sub['status'] ); ?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Campaign', 'smartpay' ); ?>"><?php echo esc_html( $sp_sub['campaign'] ? $sp_sub['campaign'] : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Amount', 'smartpay' ); ?>"><?php echo esc_html( smartpay_amount_format( $sp_sub['amount'] ) . ' / ' . ( in_array( $sp_sub['period'], array( '', 'Monthly' ), true ) ? __( 'month', 'smartpay' ) : $sp_sub['period'] ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Started', 'smartpay' ); ?>"><?php echo esc_html( date_i18n( 'M d, Y', strtotime( $sp_sub['created_at'] ) ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'smartpay' ); ?>"><span class="sp-dash-badge <?php echo esc_attr( $sp_badge['class'] ); ?>"><?php echo esc_html( $sp_badge['label'] ); ?></span></td>
							<td class="sp-dash-table__actions">
								<?php
								/**
								 * Actions for a monthly gift (Pro adds "Cancel").
								 *
								 * @param array $sp_sub Monthly gift.
								 */
								do_action( 'smartpay_dashboard_giving_monthly_actions', $sp_sub );
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif; ?>

<?php if ( $sp_giving['campaigns'] ) : ?>
	<div class="sp-dash-panel">
		<div class="sp-dash-panel__head"><h2><?php esc_html_e( 'Campaigns you support', 'smartpay' ); ?></h2></div>
		<ul class="sp-dash-giving-campaigns">
			<?php foreach ( $sp_giving['campaigns'] as $sp_camp ) : ?>
				<?php $sp_link = get_term_link( (int) $sp_camp['id'], SMARTPAY_CAMPAIGN_TAXONOMY ); ?>
				<li>
					<a href="<?php echo esc_url( is_wp_error( $sp_link ) ? '#' : $sp_link ); ?>"><strong><?php echo esc_html( $sp_camp['title'] ); ?></strong></a>
					<span>
						<?php
						/* translators: 1: amount, 2: gift count */
						printf( esc_html( _n( 'You gave %1$s · %2$d gift', 'You gave %1$s · %2$d gifts', $sp_camp['gifts'], 'smartpay' ) ), esc_html( smartpay_amount_format( $sp_camp['total'] ) ), (int) $sp_camp['gifts'] );
						?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="sp-dash-panel">
	<div class="sp-dash-panel__head"><h2><?php esc_html_e( 'Donation history', 'smartpay' ); ?></h2></div>
	<div class="sp-dash-table-wrap">
		<table class="sp-dash-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'smartpay' ); ?></th>
					<th><?php esc_html_e( 'Campaign', 'smartpay' ); ?></th>
					<th><?php esc_html_e( 'Frequency', 'smartpay' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'smartpay' ); ?></th>
					<th><?php esc_html_e( 'Status', 'smartpay' ); ?></th>
					<th class="sp-dash-table__actions"><?php esc_html_e( 'Receipt', 'smartpay' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $sp_giving['history'] as $sp_gift ) : ?>
					<?php $sp_badge = smartpay_dashboard_status_badge( $sp_gift['status'] ); ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Date', 'smartpay' ); ?>"><?php echo esc_html( date_i18n( 'M d, Y', strtotime( $sp_gift['created_at'] ) ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Campaign', 'smartpay' ); ?>"><?php echo esc_html( $sp_gift['campaign'] ? $sp_gift['campaign']['title'] : $sp_gift['form'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Frequency', 'smartpay' ); ?>"><?php echo 'monthly' === $sp_gift['frequency'] ? esc_html__( 'Monthly', 'smartpay' ) : esc_html__( 'One-time', 'smartpay' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Amount', 'smartpay' ); ?>"><?php echo esc_html( smartpay_amount_format( $sp_gift['amount'] ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'smartpay' ); ?>"><span class="sp-dash-badge <?php echo esc_attr( $sp_badge['class'] ); ?>"><?php echo esc_html( $sp_badge['label'] ); ?></span></td>
						<td data-label="<?php esc_attr_e( 'Receipt', 'smartpay' ); ?>" class="sp-dash-table__actions">
							<a href="<?php echo esc_url( smartpay_dashboard_order_url( $sp_gift['id'] ) ); ?>" class="sp-dash-btn sp-dash-btn--sm"><?php esc_html_e( 'View', 'smartpay' ); ?></a>
							<?php
							/**
							 * Extra receipt actions for a gift (Pro adds "Download PDF").
							 *
							 * @param array $sp_gift Gift row.
							 */
							do_action( 'smartpay_dashboard_giving_receipt_actions', $sp_gift );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="sp-dash-panel">
	<div class="sp-dash-panel__head"><h2><?php esc_html_e( 'Privacy', 'smartpay' ); ?></h2></div>
	<?php if ( $sp_saved ) : ?>
		<p class="sp-dash-giving-notice" role="status"><?php esc_html_e( 'Privacy setting saved.', 'smartpay' ); ?></p>
	<?php endif; ?>
	<form method="post" class="sp-dash-giving-privacy">
		<?php wp_nonce_field( 'smartpay_donor_privacy', 'smartpay_donor_privacy_nonce' ); ?>
		<input type="hidden" name="smartpay_donor_privacy" value="1" />
		<label>
			<input type="checkbox" name="hide_name" value="1" <?php checked( $sp_donor['hide_name'] ); ?> />
			<span>
				<strong><?php esc_html_e( 'Hide my name on public donor walls', 'smartpay' ); ?></strong><br />
				<small><?php esc_html_e( 'Past and future gifts show as “Anonymous”. Your gifts still count toward every campaign total.', 'smartpay' ); ?></small>
			</span>
		</label>
		<button type="submit" class="sp-dash-btn sp-dash-btn--sm"><?php esc_html_e( 'Save', 'smartpay' ); ?></button>
	</form>
</div>
