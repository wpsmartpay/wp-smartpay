<?php

namespace SmartPay\Http\Controllers\Rest;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Customer;
use WP_REST_Response;

class CustomerController extends RestController
{
    /**
     * Check permissions for the posts.
     *
     * @param \WP_REST_Request $request.
     */
    public function middleware(\WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.'), [
                'status' => is_user_logged_in() ? 403 : 401,
            ]);
        }

        return true;
    }

    public function index(\WP_REST_Request $request)
    {
        return new WP_REST_Response(Customer::all());
    }

    public function store(\WP_REST_Request $request)
    {
        dd($request->get_body());

        $product =  Customer::create([
            //
        ]);
    }
}