<?php defined('ABSPATH') || exit; ?>
<div class="section-header">
	<h2>Security Settings</h2>
	<p>Update your password and security preferences</p>
</div>

<form id="security-form" class="profile-form">
	<div class="form-group">
		<label for="current_password">Current Password <span class="required">*</span></label>
		<div class="password-input-wrapper">
			<input
				type="password"
				id="current_password"
				name="current_password"
				class="form-control"
				required
			>
			<button type="button" class="password-toggle" data-target="current_password">
				<svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
					<path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
					<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
				</svg>
			</button>
		</div>
		<span class="error-message"></span>
	</div>
	<div class="form-group-row">
		<div class="form-group">
			<label for="new_password">New Password <span class="required">*</span></label>
			<div class="password-input-wrapper">
				<input
					type="password"
					id="new_password"
					name="new_password"
					class="form-control"
					minlength="8"
					required
				>
				<button type="button" class="password-toggle" data-target="new_password">
					<svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
						<path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
						<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
					</svg>
				</button>
			</div>
			<div class="password-strength"></div>
			<span class="help-text">Minimum 8 characters with letters and numbers</span>
			<span class="error-message"></span>
		</div>

		<div class="form-group">
			<label for="confirm_new_password">Confirm New Password <span class="required">*</span></label>
			<div class="password-input-wrapper">
				<input
					type="password"
					id="confirm_new_password"
					name="confirm_new_password"
					class="form-control"
					required
				>
				<button type="button" class="password-toggle" data-target="confirm_new_password">
					<svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
						<path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
						<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
					</svg>
				</button>
			</div>
			<span class="error-message"></span>
		</div>
	</div>
	<div class="security-notice">
		<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
			<path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 15C9.45 15 9 14.55 9 14C9 13.45 9.45 13 10 13C10.55 13 11 13.45 11 14C11 14.55 10.55 15 10 15ZM11 11H9V5H11V11Z"/>
		</svg>
		<span>You'll be logged out from all devices after changing your password</span>
	</div>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary">
			<span class="btn-text">Update Password</span>
			<svg class="btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<button type="button" class="btn btn-secondary">Cancel</button>
	</div>
	<div class="form-message"></div>
</form>
