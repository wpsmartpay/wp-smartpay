<?php

namespace SmartPay\Forms;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}
final class Form
{
	/**
	 * The single instance of this class
	 */
	private static $instance = null;

	/**
	 * Construct Form class.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function __construct()
	{
	}

	/**
	 * Main Form Instance.
	 *
	 * Ensures that only one instance of Form exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 0.1
	 * @return object|Form
	 * @access public
	 */
	public static function instance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof Form)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}