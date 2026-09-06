<?php
defined( 'ABSPATH' ) || exit;

/**
 * Reusable deprecation banner (amber/warning) for paused features in 3.0.0.
 *
 * @var array $smartpay_view_data {
 *   @type string $feature Optional feature label (e.g. "Legacy Forms").
 *   @type string $title   Optional heading override.
 *   @type string $message Optional body override.
 * }
 */
$smartpay_dep_feature = $smartpay_view_data['feature'] ?? '';
$smartpay_dep_title   = $smartpay_view_data['title'] ?? __( 'This feature is paused in 3.0.0', 'smartpay' );

if ( ! empty( $smartpay_view_data['message'] ) ) {
	$smartpay_dep_message = $smartpay_view_data['message'];
} elseif ( '' !== $smartpay_dep_feature ) {
	/* translators: %s: feature name, e.g. "Legacy Forms". */
	$smartpay_dep_message = sprintf( __( '%s is paused in this release and will return in a future update. Existing data is safe, but creating new items is discouraged.', 'smartpay' ), $smartpay_dep_feature );
} else {
	$smartpay_dep_message = __( 'This feature is paused in this release and will return in a future update.', 'smartpay' );
}
?>
<div class="smartpay-deprecated-banner" role="alert">
	<span class="smartpay-deprecated-banner__icon" aria-hidden="true">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
			<line x1="12" y1="9" x2="12" y2="13"/>
			<line x1="12" y1="17" x2="12.01" y2="17"/>
		</svg>
	</span>
	<div class="smartpay-deprecated-banner__body">
		<div class="smartpay-deprecated-banner__title"><?php echo esc_html( $smartpay_dep_title ); ?></div>
		<div class="smartpay-deprecated-banner__text"><?php echo esc_html( $smartpay_dep_message ); ?></div>
	</div>
</div>
