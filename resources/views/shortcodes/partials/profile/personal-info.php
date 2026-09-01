<?php defined('ABSPATH') || exit; ?>
<div class="section-header">
	<h2>Personal Information</h2>
	<p>Update your personal details and contact information</p>
</div>

<!-- Avatar Upload Section -->
<div class="avatar-upload-section">
	<div class="avatar-preview-container">
		<div class="avatar-current">
			<?php echo get_avatar($current_user->ID, 100, '', '', ['class' => 'avatar-img']); ?>
		</div>
		<div class="avatar-preview" id="avatar-preview-wrapper" style="display: none;">
			<img id="avatar-preview" class="avatar-img" src="" alt="Preview">
		</div>
	</div>

	<div class="avatar-actions">
		<h3>Profile Picture</h3>
		<p>Update your profile picture. JPG, PNG, GIF. Max size 2MB.</p>

		<input type="file" id="avatar-upload" accept="image/jpeg,image/png,image/gif" style="display: none;">

		<div class="avatar-buttons">
			<button type="button" class="btn btn-sm btn-primary" id="choose-avatar-btn">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
					<path d="M14 10V12.6667C14 13.0203 13.8595 13.3594 13.6095 13.6095C13.3594 13.8595 13.0203 14 12.6667 14H3.33333C2.97971 14 2.64057 13.8595 2.39052 13.6095C2.14048 13.3594 2 13.0203 2 12.6667V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M11.3333 5.33333L8 2L4.66667 5.33333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M8 2V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				Choose Photo
			</button>

			<button type="button" class="btn btn-sm btn-outline" id="save-avatar-btn" style="display: none;">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
					<path d="M13 4L6 11L3 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				Save Photo
			</button>

			<button type="button" class="btn btn-sm btn-secondary" id="cancel-avatar-btn" style="display: none;">Cancel</button>
			<button type="button" class="btn btn-sm btn-secondary" id="remove-avatar-btn">Remove</button>
		</div>

		<div id="avatar-upload-progress" class="upload-progress" style="display: none;">
			<div class="progress-bar">
				<div class="progress-fill"></div>
			</div>
			<span class="progress-text">Uploading...</span>
		</div>

		<div id="avatar-message" class="avatar-message"></div>
	</div>
</div>

<form id="personal-info-form" class="profile-form">
	<div class="form-group-row">
		<div class="form-group">
			<label for="first_name">First Name <span class="required">*</span></label>
			<input
				type="text"
				id="first_name"
				name="first_name"
				class="form-control"
				value="<?php echo esc_attr($customer->first_name); ?>"
				required
			>
			<span class="error-message"></span>
		</div>

		<div class="form-group">
			<label for="last_name">Last Name <span class="required">*</span></label>
			<input
				type="text"
				id="last_name"
				name="last_name"
				class="form-control"
				value="<?php echo esc_attr($customer->last_name); ?>"
				required
			>
			<span class="error-message"></span>
		</div>
	</div>

	<div class="form-group">
		<label for="email">Email Address <span class="required">*</span></label>
		<div class="input-with-icon">
			<svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M3 4H17C17.55 4 18 4.45 18 5V15C18 15.55 17.55 16 17 16H3C2.45 16 2 15.55 2 15V5C2 4.45 2.45 4 3 4Z" stroke="currentColor" stroke-width="1.5"/>
				<path d="M18 5L10 11L2 5" stroke="currentColor" stroke-width="1.5"/>
			</svg>
			<input
				type="email"
				id="email"
				name="email"
				class="form-control with-icon"
				value="<?php echo esc_attr($current_user->user_email); ?>"
				required
			>
		</div>
		<span class="help-text">This email will be used for all account communications</span>
		<span class="error-message"></span>
	</div>

	<div class="form-group">
		<label for="phone">Phone Number</label>
		<div class="input-with-icon">
			<svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M18 14.5V17C18 17.2652 17.8946 17.5196 17.7071 17.7071C17.5196 17.8946 17.2652 18 17 18C13.5 17.5 10.5 15.5 8 13C5.5 10.5 3.5 7.5 3 4C3 3.73478 3.10536 3.48043 3.29289 3.29289C3.48043 3.10536 3.73478 3 4 3H6.5C6.63261 3 6.75979 3.05268 6.85355 3.14645C6.94732 3.24021 7 3.36739 7 3.5C7 4.5 7.19 5.39 7.5 6.17C7.54 6.28 7.52 6.41 7.44 6.5L6 8C7 10 10 13 12 14L13.5 12.56C13.59 12.48 13.72 12.46 13.83 12.5C14.61 12.81 15.5 13 16.5 13C16.6326 13 16.7598 13.0527 16.8536 13.1464C16.9473 13.2402 17 13.3674 17 13.5V14.5Z" stroke="currentColor" stroke-width="1.5"/>
			</svg>
			<input
				type="tel"
				id="phone"
				name="phone"
				class="form-control with-icon"
				value="<?php echo esc_attr($customer->phone ?? ''); ?>"
			>
		</div>
		<span class="error-message"></span>
	</div>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary">
			<span class="btn-text">Save Changes</span>
			<svg class="btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<button type="button" class="btn btn-secondary">Cancel</button>
	</div>
	<div class="form-message"></div>
</form>
