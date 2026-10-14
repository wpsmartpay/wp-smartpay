<?php defined('ABSPATH') || exit; ?>
<div class="section-header">
	<h2>Address Information</h2>
	<p>Keep your address up to date for billing purposes</p>
</div>

<form id="address-form" class="profile-form">
	<div class="form-group">
		<label for="address_line_1">Street Address <span class="required">*</span></label>
		<input
			type="text"
			id="address_line_1"
			name="address_line_1"
			class="form-control"
			value="<?php echo esc_attr($customer->address_line_1 ?? ''); ?>"
			required
		>
		<span class="error-message"></span>
	</div>

	<div class="form-group">
		<label for="address_line_2">Apartment, suite, etc.</label>
		<input
			type="text"
			id="address_line_2"
			name="address_line_2"
			class="form-control"
			value="<?php echo esc_attr($customer->address_line_2 ?? ''); ?>"
		>
	</div>

	<div class="form-group-row">
		<div class="form-group">
			<label for="city">City <span class="required">*</span></label>
			<input
				type="text"
				id="city"
				name="city"
				class="form-control"
				value="<?php echo esc_attr($customer->city ?? ''); ?>"
				required
			>
			<span class="error-message"></span>
		</div>

		<div class="form-group">
			<label for="state">State / Province <span class="required">*</span></label>
			<input
				type="text"
				id="state"
				name="state"
				class="form-control"
				value="<?php echo esc_attr($customer->state ?? ''); ?>"
				required
			>
			<span class="error-message"></span>
		</div>
	</div>

	<div class="form-group-row">
		<div class="form-group">
			<label for="postal_code">Postal Code <span class="required">*</span></label>
			<input
				type="text"
				id="postal_code"
				name="postal_code"
				class="form-control"
				value="<?php echo esc_attr($customer->postal_code ?? ''); ?>"
				required
			>
			<span class="error-message"></span>
		</div>

		<?php include SMARTPAY_DIR . 'resources/views/shortcodes/partials/countries.php'; ?>
		<div class="form-group">
			<label for="country">Country <span class="required">*</span></label>
			<select
				id="country"
				name="country"
				class="form-control"
				required
			>
				<option value="">Select Country</option>
			<?php
				foreach (smartpay_get_countries() as $code => $name) {
					printf('<option value="%s" %s>%s</option>', esc_attr( $code ), selected( $customer->country ?? '', $code, false ), esc_html( $name ) );
				}
			?>
			</select>
			<span class="error-message"></span>
		</div>
	</div>

	<div class="form-actions margin-0">
		<button type="submit" class="btn btn-primary">
			<span class="btn-text">Save Address</span>
			<svg class="btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<button type="button" class="btn btn-secondary">Cancel</button>
	</div>
	<div class="form-message"></div>
</form>
