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
		$perPage    = (int) ($request->get_param('per_page') ?: 10);
		$search     = sanitize_text_field($request->get_param('search') ?: '');
		$status     = sanitize_text_field($request->get_param('status') ?: '');
		$type       = sanitize_text_field($request->get_param('type') ?: '');
		$customerId = absint($request->get_param('customer_id') ?: 0);
		$orderBy    = sanitize_text_field($request->get_param('sort_by') ?: 'id:desc');
		// Payments and Donations are separate lists; no param = both (customer page).
		$donation   = $request->get_param('donation');

		// Start building the query
		$query = Payment::with(['customer']);

		// Apply customer filter if provided
		if (!empty($customerId)) {
			$query->where('customer_id', (int) $customerId);
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

		if (null !== $donation && '' !== $donation) {
			$query->where('is_donation', rest_sanitize_boolean($donation) ? 1 : 0);
		}

		$allowed_columns = ['id', 'email', 'transaction_id', 'amount', 'created_at', 'completed_at'];
		$orderByParts = explode(',', $orderBy);
		foreach ($orderByParts as $part) {
			$pieces    = explode(':', trim($part));
			$sortBy    = in_array($pieces[0] ?? '', $allowed_columns, true) ? $pieces[0] : 'id';
			$sortOrder = strtoupper($pieces[1] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
			$query->orderBy($sortBy, $sortOrder);
		}

		// Get paginated results
		$payments = $query->paginate($perPage);

		if (null !== $donation && rest_sanitize_boolean($donation)) {
			$this->attach_campaign_titles($payments);
		}

		$response = ['payments' => $payments];

		// If filtering by customer, include payment statistics
		if (!empty($customerId)) {
			// Stats follow the same donation filter as the list.
			$base = static function () use ($customerId, $donation) {
				$q = Payment::where('customer_id', $customerId);
				if (null !== $donation && '' !== $donation) {
					$q->where('is_donation', rest_sanitize_boolean($donation) ? 1 : 0);
				}
				return $q;
			};

			$totalPayments = $base()->count();
			$completedPayments = $base()->where('status', Payment::COMPLETED)->count();
			$pendingPayments = $base()->where('status', Payment::PENDING)->count();
			$refundedPayments = $base()->where('status', Payment::REFUNDED)->count();

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
     * Add `campaign_title` to each donation row. Renewals carry no form id, so
     * they use their parent's.
     *
     * @param iterable $payments Payment models on the current page.
     */
    private function attach_campaign_titles($payments): void
    {
		$parent_ids = array();
		foreach ($payments as $payment) {
			if ((int) $payment->parent_id > 0) {
				$parent_ids[] = (int) $payment->parent_id;
			}
		}

		$parent_forms = array();
		if ($parent_ids) {
			foreach (Payment::whereIn('id', array_unique($parent_ids))->get() as $parent) {
				$parent_forms[(int) $parent->id] = absint($parent->data['form_id'] ?? 0);
			}
		}

		$titles = array();
		foreach ($payments as $payment) {
			$form_id = (int) $payment->parent_id > 0
				? ($parent_forms[(int) $payment->parent_id] ?? 0)
				: absint($payment->data['form_id'] ?? 0);

			if (! array_key_exists($form_id, $titles)) {
				$campaign_id      = $form_id ? smartpay_get_form_campaign_id($form_id) : 0;
				$term             = $campaign_id ? get_term($campaign_id, SMARTPAY_CAMPAIGN_TAXONOMY) : null;
				$titles[$form_id] = $term instanceof \WP_Term ? $term->name : '';
			}

			$payment->campaign_title = $titles[$form_id];
		}
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

            // Form-builder (CPT) forms first: their post ids can collide with
            // rows in the legacy forms table, which must not win the lookup.
            $post   = get_post( $form_id );
            $is_cpt = $post && 'smartpay_form' === $post->post_type;
            $form   = $is_cpt ? null : \SmartPay\Models\Form::find( $form_id );

            if ( $is_cpt ) {
                $data['data']['form_type']     = 'native';
                $data['data']['form_title']    = '' !== $post->post_title ? esc_html( $post->post_title ) : sprintf( 'Form #%d', $form_id );
                $data['data']['form_edit_url'] = esc_url( admin_url( 'post.php?post=' . $form_id . '&action=edit' ) );
            } elseif ( $form ) {
                $data['data']['form_type']     = 'legacy';
                $data['data']['form_title']    = esc_html( $form->title );
                $data['data']['form_edit_url'] = esc_url( admin_url( 'admin.php?page=smartpay-form&id=' . $form_id ) );
            } else {
                $data['data']['form_type']     = '';
                $data['data']['form_title']    = sprintf( 'Form #%d (deleted)', $form_id );
                $data['data']['form_edit_url'] = '';
            }

            // Donation card: campaign + what the donor entered with the gift.
            if ( smartpay_is_donation_form( $form_id ) ) {
                $campaign_id      = smartpay_get_form_campaign_id( $form_id );
                $campaign         = $campaign_id ? smartpay_get_campaign( $campaign_id ) : null;
                $data['donation'] = array_merge(
                    smartpay_get_payment_donation( $data['extra'] ?? array() ),
                    array(
                        'campaign' => $campaign
                            ? array(
                                'id'    => $campaign['id'],
                                'title' => $campaign['title'],
                            )
                            : null,
                    )
                );
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

        $body = json_decode($request->get_body(), true);

        $allowed_statuses = [
            Payment::COMPLETED,
            Payment::PENDING,
            Payment::REFUNDED,
            Payment::ABANDONED,
            Payment::REVOKED,
            Payment::FAILED,
        ];

        $status = sanitize_text_field($body['status'] ?? '');
        if (!in_array($status, $allowed_statuses, true)) {
            return new WP_REST_Response(['message' => esc_html__('Invalid payment status.', 'smartpay')], 422);
        }

        $payment->status = $status;
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
