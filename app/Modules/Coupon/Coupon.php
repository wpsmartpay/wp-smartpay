<?php

namespace SmartPay\Modules\Coupon;
defined('ABSPATH') || exit;

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
        // Render the coupon section just before the submit button (not at the top of the form).
        $this->app->addAction('before_smartpay_payment_form_button', [$this, 'smartpayCouponPaymentForm'], 10, 1);
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
        $form_id = isset($form->id) ? (int) $form->id : 0;

        // Coupon visibility is controlled solely by the per-form setting
        // (Form Settings → Enable Coupon). Default off; the global option no
        // longer forces it on, so turning it off here always hides the coupon.
        $settings = $form_id ? get_post_meta($form_id, '_smartpay_settings', true) : '';
        $settings = is_string($settings) ? json_decode($settings, true) : (is_array($settings) ? $settings : []);

        if (empty($settings['enable_coupon'])) {
            return;
        }

        // Text + colors come from the Submit Button's Coupon child block (styling
        // only); defaults are used when the child is absent.
        $toggle_label = __('Have a coupon?', 'smartpay');
        $placeholder  = __('Coupon code', 'smartpay');
        $apply_label  = __('Apply', 'smartpay');
        $accent       = '#28a745';

        $coupon = function_exists('smartpay_get_submit_child_attrs')
            ? smartpay_get_submit_child_attrs($form_id, 'smartpay-form/submit-coupon')
            : null;

        if (is_array($coupon)) {
            $toggle_label = isset($coupon['toggleLabel']) && '' !== $coupon['toggleLabel'] ? $coupon['toggleLabel'] : $toggle_label;
            $placeholder  = isset($coupon['placeholder']) && '' !== $coupon['placeholder'] ? $coupon['placeholder'] : $placeholder;
            $apply_label  = isset($coupon['applyLabel']) && '' !== $coupon['applyLabel'] ? $coupon['applyLabel'] : $apply_label;
            $accent       = isset($coupon['accentColor']) && '' !== $coupon['accentColor'] ? $coupon['accentColor'] : $accent;
        }

        $accent = sanitize_text_field($accent);
        ?>
        <div class="smartpay-coupon" style="--sp-coupon-accent:<?php echo esc_attr($accent); ?>;">
            <a href="#" class="smartpayshowcoupon">
                <?php echo esc_html($toggle_label); ?>
            </a>
            <?php // NOTE: a <div>, not a <form> — this renders inside the main payment
            // <form>, and the HTML parser drops nested <form> elements. The Apply
            // button is type="button" so it never submits the outer payment form. ?>
            <div class="smartpay-coupon-form" style="display:none;">
                <?php wp_nonce_field('smartpay_form_coupon_action'); ?>
                <div class="smartpay-coupon-row">
                    <input type="text" name="coupon_code" id="coupon_code" class="form-control smartpay-coupon-input" placeholder="<?php echo esc_attr($placeholder); ?>" autocomplete="off" />
                    <button type="button" name="submitcoupon" class="btn smartpay-coupon-apply"><?php echo esc_html($apply_label); ?></button>
                    <button type="button" class="btn smartpay-coupon-form-close" aria-label="<?php esc_attr_e('Cancel', 'smartpay'); ?>" title="<?php esc_attr_e('Cancel', 'smartpay'); ?>">&times;</button>
                </div>
            </div>
        </div>
        <?php
    }

    public function appliedCouponInForm()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'smartpay_form_coupon_action')) {
            wp_send_json_error(['message' => __('Invalid request', 'smartpay')]);
        }

        $couponCode = isset($_POST['couponCode']) ? sanitize_text_field(wp_unslash($_POST['couponCode'])) : null;
        $formId = isset($_POST['formId']) ? sanitize_text_field(wp_unslash($_POST['formId'])) : null;
        $coupon = ModelsCoupon::where('title', $couponCode)->first();
        if (!$coupon) {
            wp_send_json_error(['message' => __('Coupon Not Found', 'smartpay')]);
        }

        // expiry date check
        if ($this->validateDate($coupon->expiry_date)) {
            $currentDate = date_create(gmdate('Y-m-d H:i:s'));
            $expiryDate = date_create($coupon->expiry_date);
            $diff = date_diff($currentDate,  $expiryDate);
            if ($diff->format("%R%a") < 0) {
                wp_send_json_error(['message' => __('Coupon has expired', 'smartpay')]);
            }
        }

        $couponDiscountAmount = $coupon->discount_amount;
        $couponDiscountType = $coupon->discount_type;

        // Resolve the form's amounts. Legacy forms live in the Form table; native
        // (block) forms store amounts in post meta. Fall back to meta so coupons
        // work on both — otherwise couponData is empty and the frontend errors.
        $form = Form::where('id', $formId)->first();
        $formAmounts = $form ? $form->amounts : null;

        if (empty($formAmounts)) {
            $meta = get_post_meta((int) $formId, '_smartpay_amounts', true);
            $formAmounts = is_string($meta) ? json_decode($meta, true) : (is_array($meta) ? $meta : []);
        }

        if (empty($formAmounts) || !is_array($formAmounts)) {
            wp_send_json_error(['message' => __('No amounts found for this form', 'smartpay')]);
        }

        $couponData = [];

        foreach ($formAmounts as $singleAmount) {
            $amount = (float) ($singleAmount['amount'] ?? 0);

            if ($couponDiscountType == 'fixed') {
                $couponAmount   = $couponDiscountAmount;
                $discountAmount = max($amount - $couponDiscountAmount, 0);
            } elseif ($couponDiscountType == 'percent') {
                $couponAmount   = ($amount * $couponDiscountAmount) / 100;
                $discountAmount = max($amount - $couponAmount, 0);
            } else {
                $couponAmount   = 0;
                $discountAmount = $amount;
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
                <input type="text" name="coupon_code" class="m-0 form-control" placeholder="<?php esc_attr_e('Coupon code', 'smartpay'); ?>" id="coupon_code" style="flex: 1;" />
                <button class="rounded btn btn-outline-success" type="submit" name="submitcoupon"><?php esc_html_e('Apply coupon', 'smartpay'); ?></button>
            </div>
        </form>
    <?php }

    public function appliedCouponInProduct()
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'smartpay_product_coupon_action')) {
	        wp_send_json_error(['message' => __('Invalid request', 'smartpay')]);
        }

        $productId = isset($_POST['productID']) ? sanitize_text_field(wp_unslash($_POST['productID'])) : null;

        //get the product details from the database
        $originalProduct = ProductModel::where('id', $productId)->first();

        $couponCode = isset($_POST['couponCode']) ? sanitize_text_field(wp_unslash($_POST['couponCode'])) : null;
        $productPrice = isset($_POST['productPrice']) ? sanitize_text_field(wp_unslash($_POST['productPrice'])) : null;
        $productPrice =  str_replace('$', '', $productPrice);
        $coupon = ModelsCoupon::where('title', $couponCode)->first();
        if (!$coupon) {
            wp_send_json_error(['message' => __('Coupon Not Found', 'smartpay')]);
        }

        // expiry date check
        if ($this->validateDate($coupon->expiry_date)) {
            $currentDate = date_create(gmdate('Y-m-d H:i:s'));
            $expiryDate = date_create($coupon->expiry_date);
            $diff = date_diff($currentDate,  $expiryDate);
            if ($diff->format("%R%a") < 0) {
                wp_send_json_error(['message' => __('Coupon has expired', 'smartpay')]);
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
