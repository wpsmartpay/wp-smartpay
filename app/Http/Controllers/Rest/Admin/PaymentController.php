<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;
use WP_REST_Response;
use WP_REST_Request;

class PaymentController extends RestController
{
    /**
     * Check permissions for the posts.
     *
     * @param \WP_REST_Request $request.
     */
    public function middleware(WP_REST_Request $request)
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
        return new WP_REST_Response(Payment::all());
    }

    public function store(WP_REST_Request $request)
    {
        dd($request->get_body());

        $product =  Payment::create([
            //
        ]);
    }

    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $payment = Payment::find($request->get_param('id'));
        $payment->delete();

        return new WP_REST_Response(['message' => 'Payment deleted'], 200);
    }
}