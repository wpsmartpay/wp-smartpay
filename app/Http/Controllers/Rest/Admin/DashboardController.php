<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;
use WP_REST_Request;
use WP_REST_Response;

class DashboardController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request.
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

    /**
     * Get monthly report
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $payments = Payment::orderBy('id', 'DESC')->take(20);

        return new \WP_REST_Response($payments);
    }
}