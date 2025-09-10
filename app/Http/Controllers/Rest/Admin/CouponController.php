<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Coupon;
use WP_REST_Request;
use WP_REST_Response;

class CouponController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request.
     */
    public function middleware(WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.', 'smartpay'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }
        return true;
    }

    /**
     * Get all coupons
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $coupons = Coupon::orderBy('id', 'DESC')->get();

        return new WP_REST_Response(['coupons' => $coupons]);
    }

    /**
     * Create new coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function store(WP_REST_Request $request): WP_REST_Response
    {
        $request = \json_decode($request->get_body(), true);

        $coupon = new Coupon();
        $coupon->title           = $request['title'];
        $coupon->description     = $request['description'];
        $coupon->discount_type   = $request['discount_type'];
        $coupon->discount_amount = $request['discount_amount'];
        $coupon->status          = Coupon::PUBLISH;
        $coupon->expiry_date     = gmdate('Y-m-d', strtotime($request['expiry_date']));
        $coupon->save();

        return new WP_REST_Response(['coupon' => $coupon, 'message' => __('Coupon created', 'smartpay')]);
    }

    /**
     * Get a coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $coupon = Coupon::find($request->get_param('id'));

        if (!$coupon) {
            return new WP_REST_Response(['message' => __('Coupon not found', 'smartpay')], 404);
        }

        return new WP_REST_Response(['coupon' => $coupon]);
    }

    /**
     * Update coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $coupon = Coupon::find($request->get_param('id'));

        if (!$coupon) {
            return new WP_REST_Response(['message' => __('Coupon not found', 'smartpay')], 404);
        }

        $request = \json_decode($request->get_body(), true);

        $coupon->title           = $request['title'];
        $coupon->description     = $request['description'];
        $coupon->discount_type   = $request['discount_type'];
        $coupon->discount_amount = $request['discount_amount'];
        $coupon->status          = Coupon::PUBLISH;
        $coupon->expiry_date     = gmdate('Y-m-d', strtotime($request['expiry_date']));
        $coupon->save();

        return new WP_REST_Response(['coupon' => $coupon, 'message' => 'Coupon updated']);
    }

    /**
     * Delete coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $coupon = Coupon::find($request->get_param('id'));

        if (!$coupon) {
            return new WP_REST_Response(['message' => __('Coupon not found', 'smartpay')], 404);
        }

        $coupon->delete();
        return new WP_REST_Response(['message' => __('Coupon deleted', 'smartpay')]);
    }
}