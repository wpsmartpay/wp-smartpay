<?php
defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$customer = smartpay_get_customer_by_user_id($current_user->ID);
?>

<div class="smartpay-profile-edit">
	<div class="profile-container">
		<div class="dashboard-layout">
			<!-- Sidebar Navigation -->
			<?php include SMARTPAY_DIR . 'resources/views/shortcodes/partials/sidebar.php'; ?>

			<!-- Main Content -->
			<div class="profile-main">
				<!-- Profile Header -->
				<div class="profile-header">
					<div class="header-content">
						<h1>Profile Settings</h1>
						<p class="subtitle">Manage your account information and security</p>
					</div>
				</div>

				<!-- Profile Tabs -->
				<div class="profile-tabs">
					<a href="#personal-info" class="tab-item active" data-tab="personal-info">
						<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
							<path d="M15 16V14C15 12.9391 14.5786 11.9217 13.8284 11.1716C13.0783 10.4214 12.0609 10 11 10H5C3.93913 10 2.92172 10.4214 2.17157 11.1716C1.42143 11.9217 1 12.9391 1 14V16" stroke="currentColor" stroke-width="1.5"/>
							<circle cx="8" cy="4" r="4" stroke="currentColor" stroke-width="1.5"/>
						</svg>
						Personal Information
					</a>
					<a href="#address" class="tab-item" data-tab="address">
						<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
							<path d="M9 1L1 5V9C1 12.87 4.26 16.3 9 17C13.74 16.3 17 12.87 17 9V5L9 1Z" stroke="currentColor" stroke-width="1.5"/>
						</svg>
						Address
					</a>
					<a href="#security" class="tab-item" data-tab="security">
						<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
							<rect x="3" y="8" width="12" height="9" rx="2" stroke="currentColor" stroke-width="1.5"/>
							<path d="M6 8V5C6 3.67392 6.52678 2.40215 7.46447 1.46447C8.40215 0.52678 9.67392 0 11 0C12.3261 0 13.5979 0.52678 14.5355 1.46447C15.4732 2.40215 16 3.67392 16 5V8" stroke="currentColor" stroke-width="1.5"/>
						</svg>
						Security
					</a>
				</div>

				<!-- Profile Forms -->
				<div class="profile-content">
					<!-- Personal Information -->
					<div id="personal-info" class="form-section active">
						<?php include SMARTPAY_DIR . 'resources/views/shortcodes/partials/profile/personal-info.php'; ?>
					</div>

					<!-- Address -->
					<div id="address" class="form-section">
						<?php include SMARTPAY_DIR . 'resources/views/shortcodes/partials/profile/address.php'; ?>
					</div>

					<!-- Security -->
					<div id="security" class="form-section">
						<?php include SMARTPAY_DIR . 'resources/views/shortcodes/partials/profile/security.php'; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
