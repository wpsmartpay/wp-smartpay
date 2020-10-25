<?php

namespace SmartPay\Admin\Coupon;

defined('ABSPATH') || exit;

use SmartPay\Support\ServiceProvider;
use SmartPay\Admin\Coupon\MetaBox;

final class CouponAdminServiceProvider extends ServiceProvider
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

	private function __construct()
	{
		add_filter('enter_title_here', [$this, 'change_default_title']);

        add_filter('manage_smartpay_product_posts_columns', [$this, 'product_columns']);

        add_filter('manage_smartpay_product_posts_custom_column', [$this, 'product_column_data'], 10, 2);

        add_filter('post_row_actions', [$this, 'modify_admin_table'], 10, 2);

	}

    public static function boot()
    {
		if (!isset(self::$instance) && !(self::$instance instanceof CouponAdminServiceProvider)) {
            self::$instance = new self();
            self::$instance->metabox = new MetaBox();
        }

        return self::$instance;
	}

	/**
     * Change default "Enter title here" input
     *
     * @since 0.0.1
     * @param string $title Default title placeholder text
     * @return string $title New placeholder text
     */
    public function change_default_title($title)
    {
        if ('smartpay_coupon' == get_current_screen()->post_type) {
            return __('Enter coupon code here', 'smartpay');
        }
	}

	public function product_columns($columns)
    {
        return [
            'cb' => $columns['cb'],
            'title' => __('Code'),
            'date' => __('Date'),
        ];
	}

    public function modify_admin_table($actions, $post)
    {
        if ('smartpay_coupon' === $post->post_type) {
            unset($actions['inline hide-if-no-js']);
            unset($actions['view']);
        }

        return $actions;
    }
}
