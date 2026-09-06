<?php
defined('ABSPATH') || exit;

$settings = get_option( 'smartpay_settings', [] );
$smartpay_login_page_url = $settings['user_login_page'] ? get_permalink( $settings['user_login_page'] ) : wp_login_url();

?>

<div class="smartpay-registration-page">
	<div class="registration-container">
		<div class="registration-form-wrapper">
			<div class="registration-form-container">
				<div class="form-header">
					<h1 class="form-title">Create Your Account</h1>
					<p class="form-subtitle">Enter your details to get started</p>
				</div>

				<!-- Registration Form -->
				<form id="smartpay-registration-form" class="registration-form">
					<!-- Personal Information -->
					<div class="form-row">
						<div class="form-group">
							<label for="first_name">
								First Name <span class="required">*</span>
							</label>
							<input
								type="text"
								id="first_name"
								name="first_name"
								class="form-control"
								placeholder="John"
								autocomplete="given-name"
							>
							<span class="error-message"></span>
						</div>

						<div class="form-group">
							<label for="last_name">
								Last Name <span class="required">*</span>
							</label>
							<input
								type="text"
								id="last_name"
								name="last_name"
								class="form-control"
								placeholder="Doe"
								autocomplete="family-name"
							>
							<span class="error-message"></span>
						</div>
					</div>

					<div class="form-group">
						<label for="email">
							Email Address <span class="required">*</span>
						</label>
						<div class="input-with-icon">
							<svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
								<path d="M3 4H17C17.55 4 18 4.45 18 5V15C18 15.55 17.55 16 17 16H3C2.45 16 2 15.55 2 15V5C2 4.45 2.45 4 3 4Z" stroke="currentColor" stroke-width="1.5"/>
								<path d="M18 5L10 11L2 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<input
								type="email"
								id="email"
								name="email"
								class="form-control with-icon"
								placeholder="john.doe@example.com"
								autocomplete="email"
							>
						</div>
						<span class="error-message"></span>
					</div>

					<div class="form-group">
						<label for="phone">
							Phone Number
						</label>
						<div class="input-with-icon">
							<svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
								<path d="M18 14.5V17C18 17.2652 17.8946 17.5196 17.7071 17.7071C17.5196 17.8946 17.2652 18 17 18C13.5 17.5 10.5 15.5 8 13C5.5 10.5 3.5 7.5 3 4C3 3.73478 3.10536 3.48043 3.29289 3.29289C3.48043 3.10536 3.73478 3 4 3H6.5C6.63261 3 6.75979 3.05268 6.85355 3.14645C6.94732 3.24021 7 3.36739 7 3.5C7 4.5 7.19 5.39 7.5 6.17C7.54 6.28 7.52 6.41 7.44 6.5L6 8C7 10 10 13 12 14L13.5 12.56C13.59 12.48 13.72 12.46 13.83 12.5C14.61 12.81 15.5 13 16.5 13C16.6326 13 16.7598 13.0527 16.8536 13.1464C16.9473 13.2402 17 13.3674 17 13.5V14.5Z" stroke="currentColor" stroke-width="1.5"/>
							</svg>
							<input
								type="tel"
								id="phone"
								name="phone"
								class="form-control with-icon"
								placeholder="+1 (555) 000-0000"
								autocomplete="tel"
							>
						</div>
						<span class="error-message"></span>
					</div>

					<!-- Address Information -->
					<div class="form-group">
						<label for="address_line_1">
							Street Address <span class="required">*</span>
						</label>
						<input
							type="text"
							id="address_line_1"
							name="address_line_1"
							class="form-control"
							placeholder="123 Main Street"
							autocomplete="address-line1"
						>
						<span class="error-message"></span>
					</div>

					<div class="form-group">
						<label for="address_line_2">
							Apartment, suite, etc.
						</label>
						<input
							type="text"
							id="address_line_2"
							name="address_line_2"
							class="form-control"
							placeholder="Apt 4B"
							autocomplete="address-line2"
						>
						<span class="error-message"></span>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label for="city">
								City <span class="required">*</span>
							</label>
							<input
								type="text"
								id="city"
								name="city"
								class="form-control"
								placeholder="New York"
								autocomplete="address-level2"
							>
							<span class="error-message"></span>
						</div>

						<div class="form-group">
							<label for="state">
								State / Province <span class="required">*</span>
							</label>
							<input
								type="text"
								id="state"
								name="state"
								class="form-control"
								placeholder="NY"
								autocomplete="address-level1"
							>
							<span class="error-message"></span>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label for="postal_code">
								Postal Code <span class="required">*</span>
							</label>
							<input
								type="text"
								id="postal_code"
								name="postal_code"
								class="form-control"
								placeholder="10001"
								autocomplete="postal-code"
							>
							<span class="error-message"></span>
						</div>

						<?php include SMARTPAY_DIR . 'resources/views/shortcodes/partials/countries.php'; ?>
						<div class="form-group">
							<label for="country">
								Country <span class="required">*</span>
							</label>
							<select
								id="country"
								name="country"
								class="form-control"
								autocomplete="country"
							>
								<option value="">Select Country</option>
								<?php
									foreach (smartpay_get_countries() as $code => $name) {
										printf('<option value="%s">%s</option>', esc_attr( $code ), esc_html( $name ) );
									}
								?>
							</select>
							<span class="error-message"></span>
						</div>
					</div>

					<!-- Account Security -->
					<div class="form-row">
						<div class="form-group">
							<label for="password">
								Password <span class="required">*</span>
							</label>
							<div class="password-input-wrapper">
								<input
									type="password"
									id="password"
									name="password"
									minlength="8"
									class="form-control"
									placeholder="••••••••"
									autocomplete="new-password"
								>
								<button type="button" class="password-toggle" data-target="password">
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
							<label for="confirm_password">
								Confirm Password <span class="required">*</span>
							</label>
							<div class="password-input-wrapper">
								<input
									type="password"
									id="confirm_password"
									name="confirm_password"
									class="form-control"
									placeholder="••••••••"
									autocomplete="new-password"
								>
								<button type="button" class="password-toggle" data-target="confirm_password">
									<svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
										<path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
										<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
									</svg>
								</button>
							</div>
							<span class="error-message"></span>
						</div>
					</div>

					<div class="checkbox-group">
						<label class="checkbox-label">
							<input type="checkbox" name="agree_terms" id="agree_terms" required>
							<span class="checkbox-custom"></span>
							<span class="checkbox-text">
								I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a> <span class="required">*</span>
							</span>
						</label>
						<span class="error-message"></span>
					</div>

					<div class="checkbox-group">
						<label class="checkbox-label">
							<input type="checkbox" name="subscribe_newsletter" id="subscribe_newsletter">
							<span class="checkbox-custom"></span>
							<span class="checkbox-text">
								Send me updates about new features and special offers
							</span>
						</label>
					</div>

					<button type="submit" class="btn btn-primary btn-full">
						<span class="btn-text">Create Account</span>
						<svg class="btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
							<path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>

					<div class="form-message"></div>
				</form>

				<!-- Login Link -->
				<div class="form-footer">
					<p>Already have an account? <a href="<?php echo esc_url($smartpay_login_page_url); ?>">Sign in</a></p>
				</div>
			</div>
		</div>
	</div>
</div>
