<?php

namespace SmartPay\Http\Controllers\Rest;
use SmartPay\Http\Controllers\RestController;

use SmartPay\Framework\Http\Request;
use SmartPay\Models\Coupon;

class CouponController extends RestController
{
    /**
     * Check permissions for the posts.
     *
     * @param \WP_REST_Request $request.
     */
    public function middleware($request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }
        return true;
    }

    public function store(\WP_REST_Request $request)
    {
        $data = \json_decode($request->get_body(),true);
        $uid = get_current_user_id();
        $coupon = new Coupon();
        $coupon->title = $data['title'];
        $coupon->description = $data['description'];
        $coupon->discount_type = $data['discounttype'];
        $coupon->discount_amount = $data['amount'];
        $coupon->status = 'published';
        $coupon->created_by = $uid;
        $coupon->expiry_date = $data['expirydate'];
        $coupon->save();

        $response = new \WP_REST_Response($coupon, 200);
        return $response;
    }
}