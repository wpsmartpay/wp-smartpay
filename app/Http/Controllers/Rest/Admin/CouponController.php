<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use WP_REST_Request;
use WP_REST_Response;
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

    public function index(WP_REST_Request $request)
    {
        $products = Coupon::all();

        return new WP_REST_Response($products);
    }

    public function store(WP_REST_Request $request)
    {
        $data = \json_decode($request->get_body(), true);
        $uid = get_current_user_id();
        $coupon = new Coupon();
        $coupon->title = $data['title'];
        $coupon->description = $data['description'];
        $coupon->discount_type = $data['discount_type'];
        $coupon->discount_amount = $data['discount_amount'];
        $coupon->status = 'published';
        $coupon->created_by = $uid;
        $coupon->expiry_date = $data['expiry_date'];
        $coupon->save();

        $response = new WP_REST_Response($coupon, 200);
        return $response;
    }

    public function view(WP_REST_Request $request)
    {
        $id = $request['id'];
        $coupon = Coupon::find($id);
        if (isset($coupon)) {
            $response = new WP_REST_Response($coupon, 200);
        } else {
            $response = new WP_REST_Response($coupon, 400);
        }
        return $response;
    }

    public function update(WP_REST_Request $request)
    {
        $data = \json_decode($request->get_body(), true);
        $coupon = Coupon::find($request->get_param('id'));
        if (isset($coupon)) {
            $coupon->title = $data['title'];
            $coupon->description = $data['description'];
            $coupon->discount_type = $data['discount_type'];
            $coupon->discount_amount = $data['discount_amount'];
            $coupon->expiry_date = $data['expiry_date'];
            $coupon->save();
            $response = new WP_REST_Response(['message' => 'Coupon Updated'], 200);
        } else {
            $response = new WP_REST_Response(['message' => 'Coupon not Found'], 400);
        }

        return $response;
    }
}
