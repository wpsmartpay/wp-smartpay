<?php

namespace SmartPay\Modules\User;

defined( 'ABSPATH' ) || exit;

use SmartPay\Modules\Shortcodes\RegisterShortcode;

class User {

	public function __construct() {
		new UserRegistration();
		new UserLogin();
		new UserProfile();
		new UserDashboard();
		new LoggedInUserRegister();
		new RegisterShortcode();
	}
}
