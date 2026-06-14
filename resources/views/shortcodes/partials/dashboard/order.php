<?php
defined( 'ABSPATH' ) || exit;

/** @var int $sp_customer_id */

$sp_order_id = smartpay_dashboard_current_order_id();
$sp_order    = smartpay_get_user_payment( $sp_customer_id, $sp_order_id );

$sp_type_labels = array(
	'product_purchase' => __( 'Product Purchase', 'smartpay' ),
	'form_payment'     => __( 'Form Payment', 'smartpay' ),
);
?>

<div class="dashboard-header">
	<div class="header-content">
		<div>
			<h1><?php esc_html_e( 'Order Details', 'smartpay' ); ?></h1>
			<p class="subtitle">
				<a class="sp-dash-link" href="<?php echo esc_url( smartpay_dashboard_view_url( 'orders' ) ); ?>">&larr; <?php esc_html_e( 'Back to Orders', 'smartpay' ); ?></a>
			</p>
		</div>
	</div>
</div>

<?php if ( ! $sp_order ) : ?>
	<div class="sp-dash-panel">
		<div class="sp-dash-empty">
			<p><?php esc_html_e( 'Order not found.', 'smartpay' ); ?></p>
		</div>
	</div>
<?php else : ?>
	<?php
	$sp_badge       = smartpay_dashboard_status_badge( (string) $sp_order->status );
	$sp_type_label  = $sp_type_labels[ $sp_order->type ] ?? ucwords( str_replace( '_', ' ', (string) $sp_order->type ) );
	$sp_receipt_url = smartpay_dashboard_receipt_url( (string) $sp_order->uuid );
	$sp_data        = $sp_order->data ? json_decode( (string) $sp_order->data, true ) : array();
	$sp_item_name   = '';
	if ( is_array( $sp_data ) ) {
		$sp_item_name = $sp_data['product_name'] ?? $sp_data['form_title'] ?? $sp_data['title'] ?? ( isset( $sp_data['form_id'] ) ? get_the_title( (int) $sp_data['form_id'] ) : '' );
	}
	?>
	<div class="sp-dash-panel">
		<div class="sp-dash-panel__head">
			<h2><?php printf( /* translators: %d: order id. */ esc_html__( 'Order #%d', 'smartpay' ), (int) $sp_order->id ); ?></h2>
			<span class="sp-dash-badge <?php echo esc_attr( $sp_badge['class'] ); ?>"><?php echo esc_html( $sp_badge['label'] ); ?></span>
		</div>

		<div class="sp-dash-kv">
			<?php if ( $sp_item_name ) : ?>
				<div class="sp-dash-kv__row">
					<span class="sp-dash-kv__key"><?php esc_html_e( 'Item', 'smartpay' ); ?></span>
					<span class="sp-dash-kv__val"><?php echo esc_html( $sp_item_name ); ?></span>
				</div>
			<?php endif; ?>
			<div class="sp-dash-kv__row">
				<span class="sp-dash-kv__key"><?php esc_html_e( 'Type', 'smartpay' ); ?></span>
				<span class="sp-dash-kv__val"><?php echo esc_html( $sp_type_label ); ?></span>
			</div>
			<div class="sp-dash-kv__row">
				<span class="sp-dash-kv__key"><?php esc_html_e( 'Amount', 'smartpay' ); ?></span>
				<span class="sp-dash-kv__val"><strong><?php echo esc_html( smartpay_amount_format( $sp_order->amount, $sp_order->currency ) ); ?></strong></span>
			</div>
			<div class="sp-dash-kv__row">
				<span class="sp-dash-kv__key"><?php esc_html_e( 'Gateway', 'smartpay' ); ?></span>
				<span class="sp-dash-kv__val"><?php echo esc_html( ucfirst( (string) $sp_order->gateway ) ?: '—' ); ?></span>
			</div>
			<div class="sp-dash-kv__row">
				<span class="sp-dash-kv__key"><?php esc_html_e( 'Date', 'smartpay' ); ?></span>
				<span class="sp-dash-kv__val"><?php echo esc_html( gmdate( 'M d, Y · g:i A', strtotime( (string) $sp_order->created_at ) ) ); ?></span>
			</div>
			<?php if ( $sp_order->completed_at ) : ?>
				<div class="sp-dash-kv__row">
					<span class="sp-dash-kv__key"><?php esc_html_e( 'Completed', 'smartpay' ); ?></span>
					<span class="sp-dash-kv__val"><?php echo esc_html( gmdate( 'M d, Y · g:i A', strtotime( (string) $sp_order->completed_at ) ) ); ?></span>
				</div>
			<?php endif; ?>
			<div class="sp-dash-kv__row">
				<span class="sp-dash-kv__key"><?php esc_html_e( 'Email', 'smartpay' ); ?></span>
				<span class="sp-dash-kv__val"><?php echo esc_html( (string) $sp_order->email ); ?></span>
			</div>
		</div>

		<?php if ( $sp_receipt_url ) : ?>
			<div class="sp-dash-panel__foot">
				<a href="<?php echo esc_url( $sp_receipt_url ); ?>" class="sp-dash-btn"><?php esc_html_e( 'View Receipt', 'smartpay' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
