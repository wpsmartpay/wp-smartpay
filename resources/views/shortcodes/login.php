<?php
defined('ABSPATH') || exit;

$settings = get_option( 'smartpay_settings', [] );
$smartpay_registration_page_url = $settings['user_registration_page'] ? get_permalink( $settings['user_registration_page'] ) : wp_registration_url();

?>

<div class="smartpay-login-page">
	<div class="login-container">
		<div class="login-form-wrapper">
			<div class="login-form-container">
				<div class="form-header">
						<h1>Sign In</h1>
						<p>Enter your credentials to access your account</p>
				</div>

				<!-- Login Form -->
				<form id="smartpay-login-form" class="login-form">
						<div class="form-group">
							<label for="username">Email or Username <span class="required">*</span></label>
							<div class="input-with-icon">
								<svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M16 17V15C16 13.9391 15.5786 12.9217 14.8284 12.1716C14.0783 11.4214 13.0609 11 12 11H6C4.93913 11 3.92172 11.4214 3.17157 12.1716C2.42143 12.9217 2 13.9391 2 15V17" stroke="currentColor" stroke-width="1.5"/>
									<circle cx="9" cy="5" r="4" stroke="currentColor" stroke-width="1.5"/>
								</svg>
								<input
									type="text"
									id="username"
									name="username"
									class="form-control with-icon"
									placeholder="Enter your email or username"
									autocomplete="username"

								>
							</div>
						<span class="error-message"></span>
				</div>

				<div class="form-group">
					<label for="password">Password <span class="required">*</span></label>
							<div class="password-input-wrapper">
								<svg class="input-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<rect x="3" y="9" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
									<path d="M6 9V6C6 4.67392 6.52678 3.40215 7.46447 2.46447C8.40215 1.52678 9.67392 1 11 1C12.3261 1 13.5979 1.52678 14.5355 2.46447C15.4732 3.40215 16 4.67392 16 6V9" stroke="currentColor" stroke-width="1.5"/>
								</svg>
								<input
									type="password"
									id="password"
									name="password"
									class="form-control with-icon"
									placeholder="Enter your password"
									autocomplete="current-password"

								>
								<button type="button" class="password-toggle" data-target="password">
									<svg class="eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
										<path d="M10 4C4.5 4 2 10 2 10C2 10 4.5 16 10 16C15.5 16 18 10 18 10C18 10 15.5 4 10 4Z" stroke="currentColor" stroke-width="1.5"/>
										<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
									</svg>
								</button>
							</div>
					<span class="error-message"></span>
				</div>

				<div class="form-options">
							<label class="checkbox-label">
								<input type="checkbox" name="remember" id="remember">
								<span class="checkbox-custom"></span>
								<span class="checkbox-text">Remember me</span>
							</label>
					<a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="forgot-password">Forgot password?</a>
				</div>

				<button type="submit" class="btn btn-primary btn-full">
							<span class="btn-text">Sign In</span>
							<svg class="btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
								<path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
				</button>

				<div class="form-message"></div>
			</form>

			<!-- Sign Up Link -->
			<div class="form-footer">
				<p>Don't have an account? <a href="<?php echo esc_url($smartpay_registration_page_url); ?>">Create one</a></p>
			</div>
			</div>
		</div>
	</div>
</div>
