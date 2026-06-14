<?php
defined( 'ABSPATH' ) || exit;

$sp_current_user = wp_get_current_user();
$sp_view         = smartpay_dashboard_current_view();
$sp_customer_id  = smartpay_dashboard_current_customer_id();

$sp_view_file = SMARTPAY_DIR . 'resources/views/shortcodes/partials/dashboard/' . $sp_view . '.php';
if ( ! file_exists( $sp_view_file ) ) {
	$sp_view      = 'overview';
	$sp_view_file = SMARTPAY_DIR . 'resources/views/shortcodes/partials/dashboard/overview.php';
}
?>

<div class="smartpay-dashboard">
	<div class="dashboard-container">
		<div class="dashboard-layout">
			<!-- Sidebar Navigation -->
			<?php require SMARTPAY_DIR . 'resources/views/shortcodes/partials/sidebar.php'; ?>

			<!-- Main Content -->
			<div class="dashboard-main">
				<?php
				// Each sub-view is a self-contained page rendered on full page load.
				// $sp_customer_id and $sp_current_user are in scope for the partial.
				require $sp_view_file;
				?>
			</div>
		</div>
	</div>
</div>
