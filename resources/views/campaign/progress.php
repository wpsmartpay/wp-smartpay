<?php
/**
 * Campaign progress bar — shared by the campaign page, the
 * [smartpay_campaign_progress] shortcode and the Campaign Progress block.
 *
 * @var array $smartpay_view_data {
 *     @type array $campaign Normalised campaign (smartpay_get_campaign()).
 *     @type array $progress smartpay_calculate_campaign_progress() result.
 * }
 *
 * @package SmartPay
 */

defined( 'ABSPATH' ) || exit;

$campaign = $smartpay_view_data['campaign'] ?? null;
$progress = $smartpay_view_data['progress'] ?? null;

if ( ! $campaign || ! $progress ) {
	return;
}

$sp_type     = $campaign['goal_type'];
$sp_has_goal = $progress['target'] > 0;
$sp_days     = '';

if ( $campaign['end_date'] && 'active' === $campaign['status'] ) {
	$sp_left = (int) ceil( ( strtotime( $campaign['end_date'] . ' 23:59:59' ) - time() ) / DAY_IN_SECONDS );
	/* translators: %s: number of days */
	$sp_days = sprintf( _n( '%s day left', '%s days left', $sp_left, 'smartpay' ), number_format_i18n( $sp_left ) );
}
?>
<div class="smartpay-campaign-progress">
	<p class="smartpay-campaign-progress__headline">
		<?php if ( $sp_has_goal ) : ?>
			<?php
			printf(
				/* translators: 1: current value, 2: goal target */
				esc_html__( '%1$s of %2$s', 'smartpay' ),
				'<strong>' . esc_html( smartpay_campaign_format_value( $progress['current'], $sp_type ) ) . '</strong>',
				esc_html( smartpay_campaign_format_value( $progress['target'], $sp_type ) )
			);
			?>
			<span class="smartpay-campaign-progress__unit">
				<?php
				if ( 'donors' === $sp_type ) {
					esc_html_e( 'donors', 'smartpay' );
				} elseif ( 'donations' === $sp_type ) {
					esc_html_e( 'donations', 'smartpay' );
				} else {
					esc_html_e( 'raised', 'smartpay' );
				}
				?>
			</span>
		<?php else : ?>
			<strong><?php echo esc_html( smartpay_amount_format( $progress['raised'] ) ); ?></strong>
			<span class="smartpay-campaign-progress__unit"><?php esc_html_e( 'raised', 'smartpay' ); ?></span>
		<?php endif; ?>
	</p>

	<?php if ( $sp_has_goal ) : ?>
		<div class="smartpay-campaign-progress__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $progress['percentage'] ); ?>">
			<span class="smartpay-campaign-progress__bar" style="width:<?php echo esc_attr( (string) $progress['percentage'] ); ?>%"></span>
		</div>
	<?php endif; ?>

	<p class="smartpay-campaign-progress__meta">
		<span>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: donation count, 2: donor count */
					__( '%1$s · %2$s', 'smartpay' ),
					/* translators: %s: number of donations */
					sprintf( _n( '%s donation', '%s donations', $progress['donations'], 'smartpay' ), number_format_i18n( $progress['donations'] ) ),
					/* translators: %s: number of donors */
					sprintf( _n( '%s donor', '%s donors', $progress['donors'], 'smartpay' ), number_format_i18n( $progress['donors'] ) )
				)
			);
			if ( $sp_days ) {
				echo ' · ' . esc_html( $sp_days );
			}
			?>
		</span>
		<?php if ( $sp_has_goal ) : ?>
			<span class="smartpay-campaign-progress__pct"><?php echo esc_html( round( $progress['percentage'] ) . '%' ); ?></span>
		<?php endif; ?>
	</p>
</div>
