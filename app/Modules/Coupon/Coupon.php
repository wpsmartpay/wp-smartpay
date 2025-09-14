<?php

namespace SmartPay\Modules\Coupon;

use SmartPay\Http\Controllers\Rest\Admin\CouponController;
use SmartPay\Models\Coupon as ModelsCoupon;
use SmartPay\Models\Form;
use SmartPay\Models\Product as ProductModel;
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
        $this->app->addAction('before_smartpay_payment_form', [$this, 'showAlert'], 10, 1);
        $this->app->addAction('before_smartpay_payment_form', [$this, 'smartpayCouponPaymentForm'], 20, 1);
        $this->app->addAction('before_smartpay_payment_form_button', [$this, 'showAppliedCouponData'], 20, 1);
        $this->app->addAction('smartpay_before_product_payment_form_button', [$this, 'showAppliedCouponData']);
        $this->app->addAction('smartpay_product_modal_popup_content', [$this, 'productPaymentModalContent'], 20, 1);
        $this->app->addAction('wp_ajax_smartpay_coupon', [$this, 'appliedCouponInForm']);
        $this->app->addAction('wp_ajax_nopriv_smartpay_coupon', [$this, 'appliedCouponInForm']);
        $this->app->addAction('wp_ajax_smartpay_product_coupon', [$this, 'appliedCouponInProduct']);
        $this->app->addAction('wp_ajax_nopriv_smartpay_product_coupon', [$this, 'appliedCouponInProduct']);
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
        $settings['main']['coupon_settings_for_form'] = [
            'id'   => 'coupon_settings_for_form',
            'name' => __('Enable coupons at form', 'smartpay'),
            'label' => __('Enable the use of coupon codes', 'smartpay'),
            'type' => 'checkbox',
        ];
        $settings['main']['coupon_settings_for_product'] = [
            'id'   => 'coupon_settings_for_product',
            'name' => __('Enable coupons at product', 'smartpay'),
            'label' => __('Enable the use of coupon codes', 'smartpay'),
            'type' => 'checkbox',
        ];
        return $settings;
    }

    public function smartpayCouponPaymentForm($form)
    {
        $enable_coupon_settings = smartpay_get_option('coupon_settings_for_form');

        if (!$enable_coupon_settings) {
            return;
        }
        ?>
        <div class="smartpay-coupon-form-toggle">
            <div class="coupon-info mb-4 p-4 bg-light">
                <?php esc_html_e('Have a coupon?', 'smartpay'); ?>
                <a href="#" class="smartpayshowcoupon"><?php esc_html_e('Click here to enter your code', 'smartpay'); ?></a>
            </div>
        </div>
        <form class="smartpay-coupon-form px-4 py-5 bg-light d-none position-relative">
	        <?php wp_nonce_field('smartpay_form_coupon_action'); ?>
            <span class="d-inline-block smartpay-coupon-form-close position-absolute bg-danger text-white p-2">X</span>
            <div class="d-flex">
                <input type="text" name="coupon_code" class="m-0" placeholder="<?php esc_attr_e('Coupon code', 'smartpay'); ?>" id=" coupon_code" style="flex: 1;" />
                <button class="rounded" type="submit" name="submitcoupon"><?php esc_html_e('Apply coupon', 'smartpay'); ?></button>
            </div>
        </form>
        <?php
    }

    public function appliedCouponInForm()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'smartpay_form_coupon_action')) {
            wp_send_json_error(['message' => 'Invalid request']);
        }

        $couponCode = isset($_POST['couponCode']) ? sanitize_text_field(wp_unslash($_POST['couponCode'])) : null;
        $formId = isset($_POST['formId']) ? sanitize_text_field(wp_unslash($_POST['formId'])) : null;
        $coupon = ModelsCoupon::where('title', $couponCode)->first();
        if (!$coupon) {
            wp_send_json_error(['message' => 'Coupon Not Found']);
        }

        // expiry date check
        if ($this->validateDate($coupon->expiry_date)) {
            $currentDate = date_create(gmdate('Y-m-d'));
            $expiryDate = date_create($coupon->expiry_date);
            $diff = date_diff($currentDate,  $expiryDate);
            if ($diff->format("%R%a") < 0) {
                wp_send_json_error(['message' => 'Coupon expires']);
            }
        }

        $couponDiscountAmount = $coupon->discount_amount;
        $couponDiscountType = $coupon->discount_type;

        $form = Form::where('id', $formId)->first();
        $formAmounts = $form->amounts;
        $couponData = [];

        foreach ($formAmounts as $singleAmount) {
            if ($couponDiscountType == 'fixed') {
                $discountAmount = $singleAmount['amount'] - $couponDiscountAmount;
                $discountAmount = $discountAmount > 0 ? $discountAmount : 0;
                $couponAmount = $couponDiscountAmount;
            } elseif ($couponDiscountType == 'percent') {
                $discountAmount = $singleAmount['amount'] - ($singleAmount['amount'] * $couponDiscountAmount) / 100;
                $discountAmount = $discountAmount > 0 ? $discountAmount : 0;
                $couponAmount = ($singleAmount['amount'] * $couponDiscountAmount) / 100;
            }
            $couponData['_form_amount_' . $singleAmount['key']] = [
                'mainAmount'        => $singleAmount['amount'],
                'discountAmount'    =>  $discountAmount,
                'couponAmount'      =>  $couponAmount,
            ];
        }

        $currency = smartpay_get_option('currency', 'USD');
        $symbol = smartpay_get_currency_symbol($currency);

        wp_send_json_success(['message' => 'Coupon Applied Successfully', 'currency' => $symbol, 'couponData' => $couponData, 'couponCode' => $couponCode]);
        wp_die();
    }

    public function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function showAlert($form)
    {
        ?>
        <div class="smartpay-message-info">
        </div>
    <?php }

    public function showAppliedCouponData($form)
    {
        ?>
        <div class="discount-amounts-container mb-3 d-none">
            <div class="py-2">
                <p class="d-flex justify-content-between m-0">
                    <span class="font-weight-bold"><?php esc_html_e('Subtotal', 'smartpay'); ?></span>
                    <span class="subtotal-amount-value"></span>
                </p>
            </div>


            <div class="py-2">
                <p class="d-flex justify-content-between m-0">
                    <span class="coupon-amount-name font-weight-bold"></span>
                    <span class="coupon-amount-value"></span>
                </p>
            </div>


            <div class="py-2">
                <p class="d-flex justify-content-between m-0">
                    <span class="font-weight-bold"><?php esc_html_e('Total Amount', 'smartpay'); ?></span>
                    <span class="total-amount-value"></span>
                </p>
            </div>

        </div>
        <?php
    }

    public function productPaymentModalContent()
    {
        $enable_coupon_settings = smartpay_get_option('coupon_settings_for_product');

        if (!$enable_coupon_settings) {
            return;
        }
        ?>
        <div class="smartpay-product-coupon-form-toggle">
            <div class="coupon-info mb-4 p-4 bg-light">
                <?php esc_html_e('Have a coupon?', 'smartpay'); ?>
                <a href="#" class="smartpayshowcoupon"><?php esc_html_e('Click here to enter your code', 'smartpay'); ?></a>
            </div>
        </div>
        <form class="smartpay-product-coupon-form p-4 bg-light d-none">
            <?php wp_nonce_field('smartpay_product_coupon_action'); ?>
            <div class="d-flex">
                <input type="text" name="coupon_code" class="m-0" placeholder="<?php esc_attr_e('Coupon code', 'smartpay'); ?>" id="coupon_code" style="flex: 1;" />
                <button class="rounded" type="submit" name="submitcoupon"><?php esc_html_e('Apply coupon', 'smartpay'); ?></button>
            </div>
        </form>
    <?php }

    public function appliedCouponInProduct()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'smartpay_product_coupon_action')) {
	        wp_send_json_error(['message' => 'Invalid request']);
        }

        $productId = isset($_POST['productID']) ? sanitize_text_field(wp_unslash($_POST['productID'])) : null;

        //get the product details from the database
        $originalProduct = ProductModel::where('id', $productId)->first();

        $couponCode = isset($_POST['couponCode']) ? sanitize_text_field(wp_unslash($_POST['couponCode'])) : null;
        $productPrice = isset($_POST['productPrice']) ? sanitize_text_field(wp_unslash($_POST['productPrice'])) : null;
        $productPrice =  str_replace('$', '', $productPrice);
        $coupon = ModelsCoupon::where('title', $couponCode)->first();
        if (!$coupon) {
            wp_send_json_error(['message' => 'Coupon Not Found']);
        }

        // expiry date check
        if ($this->validateDate($coupon->expiry_date)) {
            $currentDate = date_create(gmdate('Y-m-d'));
            $expiryDate = date_create($coupon->expiry_date);
            $diff = date_diff($currentDate,  $expiryDate);
            if ($diff->format("%R%a") < 0) {
                wp_send_json_error(['message' => 'Coupon expires']);
            }
        }

        $couponDiscountAmount = $coupon->discount_amount;
        $couponDiscountType = $coupon->discount_type;

        //check if applied coupon once for this product

        // if ($productPrice < $originalProduct->sale_price) {
        //     wp_send_json_error(['message' => 'You have already availed this coupon code']);
        //     wp_die();
        // }
        if ($couponDiscountType == 'fixed') {
            $discountAmount = $originalProduct->sale_price - $couponDiscountAmount;
            $discountAmount = $discountAmount > 0 ? $discountAmount : 0;
            $couponAmount = $couponDiscountAmount;
        } elseif ($couponDiscountType == 'percent') {
            $discountAmount = $originalProduct->sale_price - ($originalProduct->sale_price * $couponDiscountAmount) / 100;
            $discountAmount = $discountAmount > 0 ? $discountAmount : 0;
            $couponAmount = ($originalProduct->sale_price * $couponDiscountAmount) / 100;
        }

        $couponData = [
            'mainAmount'        => $originalProduct->sale_price,
            'discountAmount'    =>  $discountAmount,
            'couponAmount'      =>  $couponAmount,
        ];

        $currency = smartpay_get_option('currency', 'USD');
        $symbol = smartpay_get_currency_symbol($currency);

        wp_send_json_success(['message' => 'Coupon Applied Successfully', 'couponCode' => $couponCode, 'couponData' => $couponData, 'currency' => $symbol]);
        wp_die();
    }
}
