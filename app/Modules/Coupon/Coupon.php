<?php

namespace SmartPay\Modules\Coupon;

use SmartPay\Http\Controllers\Rest\Admin\CouponController;
use WP_REST_Server;

class Coupon
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);

        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
        $this->app->addFilter('smartpay_settings_general', [$this, 'couponSettings']);
        $this->app->addAction('before_smartpay_payment_form', [$this, 'smartpayCouponPaymentForm'], 20, 1);
    }

    public function adminScripts()
    {
        //
    }

    public function registerRestRoutes()
    {
        $couponController = $this->app->make(CouponController::class);

        register_rest_route('smartpay/v1', 'coupons', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$couponController, 'index'],
                'permission_callback' => [$couponController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::CREATABLE,
                'callback'  => [$couponController, 'store'],
                'permission_callback' => [$couponController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1', 'coupons/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$couponController, 'show'],
                'permission_callback' => [$couponController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$couponController, 'update'],
                'permission_callback' => [$couponController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$couponController, 'destroy'],
                'permission_callback' => [$couponController, 'middleware'],
            ],
        ]);
    }

    public function couponSettings($settings)
    {
        $settings['main']['coupon_heading_settings'] = [
            'id'   => 'coupon_heading_settings',
            'name' => '<h4 class="text-uppercase text-info my-1">' . __('Coupons Settings', 'smartpay') . '</h4>',
            'desc' => '',
            'type' => 'header',
        ];
        $settings['main']['coupon_settings'] = [
            'id'   => 'coupon_settings',
            'name' => __('Enable coupons', 'smartpay'),
            'label' => __('Enable the use of coupon codes', 'smartpay'),
            'type' => 'checkbox',
        ];
        return $settings;
    }

    public function smartpayCouponPaymentForm($form)
    {
?>
<div class="p-5 smartpay-coupon-form-toogle" style="background-color:#eee;">
    <div class="coupon-info">
        <?php _e('Have a coupon?', 'smartpay'); ?>
        <a href="#" class="smartpayshowcoupon"><?php _e('Click here to enter your code', 'smartpay'); ?></a>
    </div>

    <form class="smartpaycouponform" style="display:none;">
        <p class="mt-4"><?php _e('If you have a coupon code, please apply it below.', 'smartpay'); ?></p>
        <input type="text" name="coupon_code" class="input-text w-100 m-0" placeholder="<?php _e('Coupon code', 'smartpay'); ?>" id=" coupon_code" />
        <button class="w-100 mt-3 rounded" type="submit" name="submitcoupon"><?php _e('Apply coupon', 'smartpay'); ?></button>
    </form>
</div>
<?php
    }
}