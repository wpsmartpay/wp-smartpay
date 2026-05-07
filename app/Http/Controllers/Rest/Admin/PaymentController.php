<?php

namespace SmartPay\Http\Controllers\Rest\Admin;
defined('ABSPATH') || exit;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Payment;
use SmartPay\Models\PaymentLog;
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

		$response = ['payments' => $payments];

		// If filtering by customer, include payment statistics
		if (!empty($customerId)) {
			$baseQuery = Payment::where('customer_id', $customerId);

			$totalPayments = $baseQuery->count();
			$completedPayments = Payment::where('customer_id', $customerId)->where('status', Payment::COMPLETED)->count();
			$pendingPayments = Payment::where('customer_id', $customerId)->where('status', Payment::PENDING)->count();
			$refundedPayments = Payment::where('customer_id', $customerId)->where('status', Payment::REFUNDED)->count();

			$response['payment_stats'] = [
				'total' => $totalPayments,
				'completed' => $completedPayments,
				'pending' => $pendingPayments,
				'refunded' => $refundedPayments,
			];
		}

		return new WP_REST_Response($response);
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

        $data = $payment->toArray();

        // Resolve product/form names and edit URLs.
        $raw_type = $payment->getType();

        if ( Payment::PRODUCT_PURCHASE === $raw_type && ! empty( $data['data']['product_id'] ) ) {
            $product_id = absint( $data['data']['product_id'] );
            $product    = \SmartPay\Models\Product::find( $product_id );

            $data['data']['product_title']    = $product ? esc_html( $product->title ) : sprintf( '#%d (deleted)', $product_id );
            $data['data']['product_edit_url'] = $product
                ? esc_url( admin_url( 'admin.php?page=smartpay-products&action=update&id=' . $product_id ) )
                : '';
        }

        if ( Payment::FORM_PAYMENT === $raw_type && ! empty( $data['data']['form_id'] ) ) {
            $form_id = absint( $data['data']['form_id'] );

            // Check native forms table first.
            $form = \SmartPay\Models\Form::find( $form_id );

            if ( $form ) {
                $data['data']['form_type']    = 'native';
                $data['data']['form_title']    = esc_html( $form->title );
                $data['data']['form_edit_url'] = '#/forms/' . $form_id . '/edit';
            } else {
                // Fall back to legacy WP post form.
                $legacy = get_post( $form_id );

                if ( $legacy && 'smartpay_form' === $legacy->post_type ) {
                    $data['data']['form_type']    = 'legacy';
                    $data['data']['form_title']    = esc_html( $legacy->post_title ) ?: sprintf( 'Form #%d', $form_id );
                    $data['data']['form_edit_url'] = esc_url( admin_url( 'admin.php?page=smartpay-form&id=' . $form_id ) );
                } else {
                    $data['data']['form_type']    = '';
                    $data['data']['form_title']    = sprintf( 'Form #%d (deleted)', $form_id );
                    $data['data']['form_edit_url'] = '';
                }
            }
        }

        // Resolve related subscription if any.
        if ( class_exists( '\SmartPayPro\Models\Subscription' ) ) {
            $subscription = \SmartPayPro\Models\Subscription::where( 'parent_payment_id', $payment->id )->first();
            $data['subscription_id'] = $subscription ? $subscription->id : null;
        } else {
            $data['subscription_id'] = null;
        }

        do_action( 'smartpay_payment_details_after_info', $payment );

        if ( ! empty( $data['extra']['form_data'] ) ) {
            do_action( 'smartpay_payment_details_after_form_data', $payment );
        }

        return new WP_REST_Response( array( 'payment' => $data ) );
    }

    /**
     * Get paginated logs for a payment.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function logs( WP_REST_Request $request ): WP_REST_Response {
        $payment = Payment::find( $request->get_param( 'id' ) );

        if ( ! $payment ) {
            return new WP_REST_Response( array( 'message' => __( 'Payment not found', 'smartpay' ) ), 404 );
        }

        $per_page = min( 100, max( 1, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
        $page     = max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) );

        $total  = PaymentLog::where( 'payment_id', $payment->id )->count();
        $offset = ( $page - 1 ) * $per_page;
        $items = PaymentLog::where( 'payment_id', $payment->id )
            ->orderBy( 'created_at', 'DESC' )
            ->skip( $offset )
            ->take( $per_page )
            ->get();

        $data = array_map(
            function ( $log ) {
                $arr              = $log->toArray();
                $user_id          = (int) ( $log->user_id ?? 0 );
                $arr['user_name'] = $user_id ? get_the_author_meta( 'display_name', $user_id ) : '';
                return $arr;
            },
            $items instanceof \SmartPay\Framework\Database\Eloquent\ModelCollection ? $items->all() : (array) $items
        );

        return new WP_REST_Response(
            array(
                'data'         => $data,
                'current_page' => $page,
                'per_page'     => $per_page,
                'total'        => $total,
                'last_page'    => max( 1, (int) ceil( $total / $per_page ) ),
            )
        );
    }

    /**
     * Add a manual admin note log entry for a payment.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function addLog( WP_REST_Request $request ): WP_REST_Response {
        $payment = Payment::find( $request->get_param( 'id' ) );

        if ( ! $payment ) {
            return new WP_REST_Response( array( 'message' => __( 'Payment not found', 'smartpay' ) ), 404 );
        }

        $body = json_decode( $request->get_body(), true );
        $note = isset( $body['note'] ) ? sanitize_textarea_field( $body['note'] ) : '';

        if ( empty( $note ) ) {
            return new WP_REST_Response( array( 'message' => __( 'Note is required.', 'smartpay' ) ), 422 );
        }

        $log = smartpay_record_payment_log( (int) $payment->id, 'admin_note', $note );

        return new WP_REST_Response( array( 'log' => $log, 'message' => __( 'Note added.', 'smartpay' ) ), 201 );
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
