<?php

namespace SmartPay\Admin;

use SmartPay\Admin\Settings\Register_Setting;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}
final class Setting
{
	/**
	 * The single instance of this class
	 */
	private static $instance = null;

	/**
	 * Construct Setting class.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function __construct()
	{
		Register_Setting::instance();
	}

	/**
	 * Main Setting Instance.
	 *
	 * Ensures that only one instance of Setting exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 0.1
	 * @return object|Setting
	 * @access public
	 */
	public static function instance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof Setting)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}