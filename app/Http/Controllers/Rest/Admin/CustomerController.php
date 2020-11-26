<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Customer;
use WP_REST_Request;
use WP_REST_Response;

class CustomerController extends RestController
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
     * Get all customers
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $customers = Customer::orderBy('id', 'DESC')->get();

        return new WP_REST_Response(['customers' => $customers]);
    }

    /**
     * Create new customer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function store(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response();
    }

    /**
     * Get a customer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $customer = Customer::find($request->get_param('id'));

        if (!$customer) {
            return new WP_REST_Response(['message' => __('Customer not found', 'smartpay')], 404);
        }

        return new WP_REST_Response(['customer' => $customer]);
    }

    /**
     * Update customer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $customer = Customer::find($request->get_param('id'));

        if (!$customer) {
            return new WP_REST_Response(['message' => __('Customer not found', 'smartpay')], 404);
        }

        // $request = json_decode($request->get_body());
        // $customer->save();

        return new WP_REST_Response(['customer' => $customer, 'message' => __('Customer updated', 'smartpay')]);
    }

    /**
     * Delete customer
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $customer = Customer::find($request->get_param('id'));

        if (!$customer) {
            return new WP_REST_Response(['message' => __('Customer not found', 'smartpay')], 404);
        }

        $customer->delete();
        return new WP_REST_Response(['message' => __('Customer deleted', 'smartpay')]);
    }
}
