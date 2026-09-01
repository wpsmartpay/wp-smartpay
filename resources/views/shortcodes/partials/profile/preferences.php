<?php defined('ABSPATH') || exit; ?>
<div class="section-header">
	<h2>Email Preferences</h2>
	<p>Manage your email notification settings</p>
</div>

<form id="preferences-form" class="profile-form">
	<div class="preference-item">
		<div class="preference-info">
			<h3>Marketing Emails</h3>
			<p>Receive updates about new features, offers, and promotions</p>
		</div>
		<label class="switch">
			<input type="checkbox" name="subscribe_newsletter" <?php checked($customer->subscribe_newsletter ?? '') ?>>
			<span class="slider"></span>
		</label>
	</div>

	<div class="preference-item">
		<div class="preference-info">
			<h3>Transaction Emails</h3>
			<p>Get notified about payments, receipts, and billing updates</p>
		</div>
		<label class="switch">
			<input type="checkbox" name="transaction_emails" checked disabled>
			<span class="slider"></span>
		</label>
	</div>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary">
			<span class="btn-text">Save Preferences</span>
			<svg class="btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
	</div>
	<div class="form-message"></div>
</form>
