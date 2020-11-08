<?php

namespace SmartPay\Http\Controllers\Rest;

use SmartPay\Framework\Http\Request;
//use SmartPay\Models\Product;

class CouponController extends \WP_REST_Controller
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
        dd($request->get_body());

        // $product =  Product::create([
        //     // 'title' => $title,
        // ]);
    }
}