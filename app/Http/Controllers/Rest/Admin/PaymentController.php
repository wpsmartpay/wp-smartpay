<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;
use WP_REST_Request;
use WP_REST_Response;

class PaymentController extends RestController
{
    /**
     * Check permissions for the request.
     *
     * @param WP_REST_Request $request.
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

    /**
     * Get all payments
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $payments = Payment::orderBy('id', 'DESC')->get();

        return new WP_REST_Response(['payments' => $payments]);
    }

    /**
     * Create new payment
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function store(WP_REST_Request $request): WP_REST_Response
    {
        $request = json_decode($request->get_body());

        $payment = new Payment();
        // $payment->status = Payment::PENDING;
        // $payment->save();

        return new WP_REST_Response(['payment' => $payment, 'message' => __('Payment created', 'smartpay')]);
    }

    /**
     * Get a payment
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $payment = Payment::find($request->get_param('id'));

        if (!$payment) {
            return new WP_REST_Response(['message' => __('Payment not found', 'smartpay')], 404);
        }

        return new WP_REST_Response(['payment' => $payment]);
    }

    /**
     * Update payment
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $payment = Payment::find($request->get_param('id'));

        if (!$payment) {
            return new WP_REST_Response(['message' => __('Payment not found', 'smartpay')], 404);
        }

        $request = json_decode($request->get_body());

        // $payment->status = Payment::PENDING;
        // $payment->save();

        return new WP_REST_Response(['payment' => $payment, 'message' => __('Payment updated', 'smartpay')]);
    }

    /**
     * Delete payment
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $payment = Payment::find($request->get_param('id'));

        if (!$payment) {
            return new WP_REST_Response(['message' => __('Payment not found', 'smartpay')], 404);
        }

        $payment->delete();
        return new WP_REST_Response(['message' => __('Payment deleted', 'smartpay')]);
    }
}