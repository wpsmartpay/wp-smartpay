<?php
/**
 * Self-check for NativeForm::guard_custom_billing. Dev only (scripts/ is in .distignore).
 *
 * Run: docker cp scripts/custom-billing-check.php wp-smartpay-pro-wordpress-1:/tmp/ &&
 * docker exec wp-smartpay-pro-wordpress-1 php -d zend.assertions=1 -d assert.exception=1 /tmp/custom-billing-check.php
 *
 * @package SmartPay
 */

$wp_load = '/var/www/html/wp-load.php';
require $wp_load;
$nf   = new \SmartPay\Modules\NativeForm\NativeForm( null );
$form = wp_insert_post(
	array(
		'post_type'   => 'smartpay_form',
		'post_status' => 'draft',
		'post_title'  => 'cb-check',
	)
);
update_post_meta(
	$form,
	'_smartpay_custom_billing',
	wp_json_encode(
		array(
			'mode'    => 'donor',
			'periods' => array( 'Monthly', 'Yearly' ),
		)
	)
);

$run    = function ( $custom, $period, $form_id = null ) use ( $nf, $form ) {
	$data = array(
		'payment_type'   => 'form_payment',
		'billing_type'   => 'Subscription',
		'billing_period' => $period,
		'payment_data'   => array(
			'billing_type'   => 'Subscription',
			'billing_period' => $period,
		),
	);
	$raw  = array(
		'smartpay_form_id'             => $form_id ?? $form,
		'smartpay_is_custom_amount'    => $custom,
		'smartpay_form_billing_period' => $period,
	);
	return $nf->guard_custom_billing( $data, $raw );
};
$is_sub = fn( $d ) => 'Subscription' === $d['payment_data']['billing_type'] && isset( $d['billing_period'], $d['payment_data']['billing_period'] );
$is_one = fn( $d ) => 'One Time' === $d['payment_data']['billing_type'] && ! isset( $d['billing_type'], $d['billing_period'], $d['payment_data']['billing_period'] );

$pro = smartpay_is_pro_active();
assert( $pro, 'Pro must be active for this check' );

assert( $is_sub( $run( 'true', 'Monthly' ) ), 'allowed period kept' );
assert( $is_one( $run( 'true', 'Weekly' ) ), 'period not ticked -> one time' );
assert( $is_one( $run( 'true', 'month' ) ), 'short name not allowed -> one time' );
assert( $is_sub( $run( 'false', 'year' ) ), 'card subscription with "false" string untouched' );
assert( $is_sub( $run( false, 'year' ) ), 'card subscription with bool false untouched' );
assert( $is_one( $run( 'true', 'Monthly', 999999 ) ), 'unknown form -> one time' );

update_post_meta(
	$form,
	'_smartpay_custom_billing',
	wp_json_encode(
		array(
			'mode'    => 'one_time',
			'periods' => array(),
		)
	)
);
assert( $is_one( $run( 'true', 'Monthly' ) ), 'mode one_time -> one time' );

update_post_meta(
	$form,
	'_smartpay_custom_billing',
	wp_json_encode(
		array(
			'mode'    => 'donor',
			'periods' => array( 'Monthly' ),
		)
	)
);
add_filter( 'smartpay_is_pro_active', '__return_false' );
$no_pro = ! smartpay_is_pro_active();
if ( $no_pro ) {
	assert( $is_one( $run( 'true', 'Monthly' ) ), 'no Pro -> one time' );
}
remove_filter( 'smartpay_is_pro_active', '__return_false' );

$other = array(
	'payment_type' => 'product_purchase',
	'billing_type' => 'Subscription',
);
assert( $other === $nf->guard_custom_billing( $other, array( 'smartpay_is_custom_amount' => 'true' ) ), 'non-form payment untouched' );

// Render filter: dropdown stripped only without Pro.
$html = '<div><input/><select class="form-control smartpay-custom-billing-period" name="smartpay_custom_billing_period" aria-label="Billing"><option value="">One time</option><option value="Monthly">Monthly</option></select></div>';
assert( false !== strpos( $nf->render_pricing_block( $html ), 'smartpay-custom-billing-period' ), 'dropdown kept with Pro' );
assert( '<p>x</p>' === $nf->render_pricing_block( '<p>x</p>' ), 'other markup untouched' );

wp_delete_post( $form, true );
echo 'OK' . ( $no_pro ? '' : ' (no-Pro case skipped: smartpay_is_pro_active has no filter)' ) . "\n";
