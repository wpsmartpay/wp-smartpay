<?php

namespace SmartPay\Http\Controllers\Rest\Admin;
defined('ABSPATH') || exit;

use SmartPay\Http\Controllers\RestController;
use SmartPay\Models\Coupon;
use WP_REST_Request;
use WP_REST_Response;

class CouponController extends RestController
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
     * Get all coupons
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $perPage = (int) ($request->get_param('per_page') ?: 10);
        $search  = sanitize_text_field($request->get_param('search') ?: '');
        $type    = sanitize_text_field($request->get_param('type') ?: '');

        $query = Coupon::orderBy('id', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }

        if (!empty($type)) {
            $query->where('discount_type', $type);
        }

        $coupons = $query->paginate($perPage);

        return new WP_REST_Response(['coupons' => $coupons]);
    }

    /**
     * Create new coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function store(WP_REST_Request $request): WP_REST_Response
    {
        $data   = \json_decode($request->get_body(), true);
        $errors = [];

        $title           = sanitize_text_field($data['title'] ?? '');
        $description     = sanitize_textarea_field($data['description'] ?? '');
        $discount_type   = sanitize_text_field($data['discount_type'] ?? '');
        $discount_amount = max(0, (float) ($data['discount_amount'] ?? 0));
        $expiry_date     = $this->sanitize_date($data['expiry_date'] ?? '');

        if (!$title) {
            $errors['title'] = __('Coupon Code is required.', 'smartpay');
        }

        if (!in_array($discount_type, ['fixed', 'percent'], true)) {
            $errors['discount_type'] = __('Discount type must be fixed or percent.', 'smartpay');
        }

        if ($discount_amount <= 0) {
            $errors['discount_amount'] = __('Discount amount must be greater than 0.', 'smartpay');
        }

        if ('percent' === $discount_type && $discount_amount >= 100) {
            $errors['discount_amount'] = __('Percentage discount cannot exceed 100%.', 'smartpay');
        }

        if (!empty($errors)) {
            return new WP_REST_Response([
                'error'  => __('Validation failed.', 'smartpay'),
                'errors' => $errors,
            ], 400);
        }

        $coupon = new Coupon();
        $coupon->title           = $title;
        $coupon->description     = $description;
        $coupon->discount_type   = $discount_type;
        $coupon->discount_amount = $discount_amount;
        $coupon->status          = Coupon::PUBLISH;
        $coupon->expiry_date     = $expiry_date;
        $coupon->save();

        return new WP_REST_Response(['coupon' => $coupon, 'message' => __('Coupon created', 'smartpay')]);
    }

    /**
     * Get a coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $coupon = Coupon::find($request->get_param('id'));

        if (!$coupon) {
            return new WP_REST_Response(['message' => __('Coupon not found', 'smartpay')], 404);
        }

        return new WP_REST_Response(['coupon' => $coupon]);
    }

    /**
     * Update coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $coupon = Coupon::find($request->get_param('id'));

        if (!$coupon) {
            return new WP_REST_Response(['message' => __('Coupon not found', 'smartpay')], 404);
        }

        $data   = \json_decode($request->get_body(), true);
        $errors = [];

        $title           = sanitize_text_field($data['title'] ?? '');
        $description     = sanitize_textarea_field($data['description'] ?? '');
        $discount_type   = sanitize_text_field($data['discount_type'] ?? '');
        $discount_amount = max(0, (float) ($data['discount_amount'] ?? 0));
        $expiry_date     = $this->sanitize_date($data['expiry_date'] ?? '');

        if (!$title) {
            $errors['title'] = __('Coupon Code is required.', 'smartpay');
        }

        if (!in_array($discount_type, ['fixed', 'percent'], true)) {
            $errors['discount_type'] = __('Discount type must be fixed or percent.', 'smartpay');
        }

        if ($discount_amount <= 0) {
            $errors['discount_amount'] = __('Discount amount must be greater than 0.', 'smartpay');
        }

        if ('percent' === $discount_type && $discount_amount >= 100) {
            $errors['discount_amount'] = __('Percentage discount cannot exceed 100%.', 'smartpay');
        }

        if (!empty($errors)) {
            return new WP_REST_Response([
                'error'  => __('Validation failed.', 'smartpay'),
                'errors' => $errors,
            ], 400);
        }

        $coupon->title           = $title;
        $coupon->description     = $description;
        $coupon->discount_type   = $discount_type;
        $coupon->discount_amount = $discount_amount;
        $coupon->status          = Coupon::PUBLISH;
        $coupon->expiry_date     = $expiry_date;
        $coupon->save();

        return new WP_REST_Response(['coupon' => $coupon, 'message' => __('Coupon updated', 'smartpay')]);
    }

    /**
     * Delete coupon
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $coupon = Coupon::find($request->get_param('id'));

        if (!$coupon) {
            return new WP_REST_Response(['message' => __('Coupon not found', 'smartpay')], 404);
        }

        $coupon->delete();
        return new WP_REST_Response(['message' => __('Coupon deleted', 'smartpay')]);
    }

    /**
     * Validate and sanitize a date string.
     * Returns a gmdate-formatted string on success, null on invalid input.
     *
     * @param mixed $value
     * @return string|null
     */
    private function sanitize_date($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        $value = sanitize_text_field((string) $value);
        // Accept Y-m-d or Y-m-d H:i:s
        foreach (['Y-m-d H:i:s', 'Y-m-d'] as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt && $dt->format($format) === $value) {
                return gmdate('Y-m-d H:i:s', $dt->getTimestamp());
            }
        }
        return null;
    }
}
