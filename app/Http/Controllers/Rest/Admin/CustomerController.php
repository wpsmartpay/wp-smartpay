<?php

namespace SmartPay\Http\Controllers\Rest\Admin;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Customer;
use SmartPay\Models\Payment;
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
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.', 'smartpay'), [
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
		$perPage = $request->get_param('per_page') ?: 10;
		$search = $request->get_param('search') ?: '';

        $query = Customer::orderBy('id', 'DESC');

		if (!empty($search)) {
			$query->where(function($q) use ($search) {
				$q->where('email', 'like', '%' . $search . '%');
			});
		}

		$customers = $query->paginate($perPage);

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
		$perPage = $request->get_param('per_page') ?: 10;

		$customer = Customer::find($request->get_param('id'));
        if (!$customer) {
            return new WP_REST_Response(['message' => __('Customer not found', 'smartpay')], 404);
        }

		$completed_payments = $customer->payments()->where('status', '=', Payment::COMPLETED)->count();
		$refunded_payments = $customer->payments()->where('status', '=', Payment::REFUNDED)->count();
		$pending_payments = $customer->payments()->where('status', '=', Payment::PENDING)->count();
		$customer_payments = $customer->payments()->orderBy('id', 'DESC')->paginate($perPage);

        return new WP_REST_Response([
			'customer' => $customer,
			'payments' => $customer_payments,
			'payment_stats' => [
				'completed' => $completed_payments,
				'refunded' => $refunded_payments,
				'pending' => $pending_payments,
			],
		]);
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
