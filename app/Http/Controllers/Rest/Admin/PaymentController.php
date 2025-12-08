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
            return new \WP_Error('rest_forbidden', esc_html__('You cannot view the resource.', 'smartpay'), [
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
		$perPage = $request->get_param('per_page') ?: 10;
		$search = $request->get_param('search') ?: '';
		$status = $request->get_param('status') ?: '';
		$type = $request->get_param('type') ?: '';
		$customerId = $request->get_param('customer_id') ?: '';
		$orderBy = $request->get_param('sort_by') ?: 'id:desc';

		// Start building the query
		$query = Payment::with(['customer']);

		// Apply customer filter if provided
		if (!empty($customerId)) {
			$query->where('customer_id', $customerId);
		}

		// Apply search filter if provided
		if (!empty($search)) {
			$query->where(function($q) use ($search) {
				$q->where('email', 'like', '%' . $search . '%')
				  ->orWhere('transaction_id', 'like', '%' . $search . '%');
			});
		}

		// Apply status filter if provided
		if (!empty($status)) {
			$query->where('status', $status);
		}

		// Apply type filter if provided
		if (!empty($type)) {
			$query->where('type', $type);
		}

		$orderByParts = explode(',', $orderBy);
		foreach ($orderByParts as $part) {
			[$sortBy, $sortOrder] = explode(':', $part);
			$query->orderBy($sortBy, $sortOrder);
		}

		// Get paginated results
		$payments = $query->paginate($perPage);

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
        $payment = Payment::with(['customer'])->find($request->get_param('id'));

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

        $request = json_decode($request->get_body(), true);

        $payment->status = $request['status'];
        $payment->save();

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
            return new WP_REST_Response([
				'message' => __('Payment not found', 'smartpay'),
				'status' => 404,
			], 404);
        }

        $payment->delete();
        return new WP_REST_Response([
			'message' => __('Payment deleted', 'smartpay'),
			'status' => 200,
		]);
    }
}
